package main

import (
	"bytes"
	"context"
	"fmt"
	"github.com/gogo/protobuf/proto"
	"golang.org/x/net/websocket"
	"google.golang.org/grpc"
	"google.golang.org/grpc/metadata"
	"io"
	monopoly "kek/server"
	"log"
	"net/http"
	"sync"
)

type jsMsg struct {
	reqId string
	meth string
	data []byte
}

func jsWebsocketProxy(dir, addr, svcAddr string) {

	http.Handle("/", http.FileServer(http.Dir(dir)))
	http.Handle("/app/", websocket.Handler(func(ws *websocket.Conn) {

		req := ws.Request()
		//todo check for service name

		var userId string
		if c, err := req.Cookie("user_id"); err == nil {
			userId = c.Value
		}
		var gameId string
		if c, err := req.Cookie("game_id"); err == nil {
			gameId = c.Value
		}
		log.Println("handling ws conn", req.RequestURI, req.Header, userId, gameId)

		cc, err := grpc.Dial(svcAddr, grpc.WithInsecure())
		if err != nil {
			log.Fatalln("dial svc", err)
		}
		defer cc.Close()

		client := monopoly.NewMonopolyClient(cc)

		ctx := context.Background()
		md := metadata.New(map[string]string{"user_id": userId, "game_id": gameId})
		ctx = metadata.NewOutgoingContext(ctx, md)

		c2s := make(chan jsMsg)
		s2c := make(chan jsMsg)
		go func () {
			for {
				m, ok := <-s2c
				if !ok {
					log.Println("s2c closed - stopping write")
					break
				}
				by := m.reqId + ":" + m.meth + ":" + string(m.data)
				nw, ew := ws.Write([]byte(by))

				log.Println("wrote", nw, ew, m.data, by)

				if ew != nil {
					log.Println("ws write err", ew)
					//todo propagate that s2c writer finishes
					return
				}
				if len(by) != nw {
					log.Println("ws write len", io.ErrShortWrite)
					//todo propagate that s2c writer finishes
					return
				}
			}
		}()

		go processWsMsg(c2s, s2c, ctx, client)

		buf := make([]byte, 32 * 1024)
		for {
			log.Println("reading")
			nr, err := ws.Read(buf)
			if err != nil {
				log.Println("ws read", err, "closing c2s")
				close(c2s)
				break
			}

			log.Println("read", nr, string(buf))

			//request:req_id ":" meth ":" data
			//response:req_id ":" "err"|"ok" ":" data|err_msg
			parts := bytes.SplitN(buf[0:nr], []byte{':'}, 3)

			c2s <- jsMsg{string(parts[0]), string(parts[1]), parts[2]}
		}

	}))

	log.Printf("http.ListenAndServe(%s) - dir '%s'", addr, dir)
	log.Println(http.ListenAndServe(addr, nil))
}

func processWsMsg(c2s, s2c chan jsMsg, ctx context.Context, client monopoly.MonopolyClient) {
	//, meth string, arg []byte
	//string, []byte
	var wg sync.WaitGroup
	var c2sopen bool
	var m jsMsg
	for {
		m, c2sopen = <-c2s
		if !c2sopen {
			log.Println("processWsMsg c2s closed - waiting stream recvers")
			wg.Wait()
			log.Println("processWsMsg c2s closed - closing s2c")
			close(s2c)
			break
		}
		if m.meth == "Subs" {
			req := &monopoly.SubsRequest{}
			err := proto.Unmarshal(m.data, req)
			if err != nil {
				err := fmt.Errorf("could not unmarshal: %w", err).Error()
				log.Println(err)
				s2c <- jsMsg{m.reqId, "err", []byte(err)}
				continue
			}

			resp, err := client.Subs(ctx, req)
			if err != nil {
				err := fmt.Errorf("could not call: %w", err).Error()
				log.Println(err)
				s2c <- jsMsg{m.reqId, "err", []byte(err)}
				continue
			}
			wg.Add(1)
			go func (reqId string, resp monopoly.Monopoly_SubsClient) {
				defer wg.Done()
				for {
					msg, err := resp.Recv()

					if !c2sopen {
						// service has closed connection due to client disconnected first
						break
					}

					if err != nil {
						err := fmt.Errorf("could not recv: %w", err).Error()
						log.Println(err)
						s2c <- jsMsg{reqId, "err", []byte(err)}
						break
					}

					//todo determine ending condition
					by, err := proto.Marshal(msg)
					if err != nil {
						err := fmt.Errorf("could not marshal streaming: %w", err).Error()
						log.Println(err)
						s2c <- jsMsg{reqId, "err", []byte(err)}
						break
					}
					s2c <- jsMsg{reqId, "ok", by}
				}
			}(m.reqId, resp)
		} else if m.meth == "Chat" {
			req := &monopoly.ChatRequest{}
			err := proto.Unmarshal(m.data, req)
			if err != nil {
				err := fmt.Errorf("could not unmarshal chat: %w data(%v)%v", err, len(m.data), string(m.data)).Error()
				log.Println(err)
				s2c <- jsMsg{m.reqId, "err", []byte(err)}
				continue
			}

			resp, err := client.Chat(ctx, req)
			if err != nil {
				err := fmt.Errorf("could not call: %w", err).Error()
				log.Println(err)
				s2c <- jsMsg{m.reqId, "err", []byte(err)}
				continue
			}

			by, err := proto.Marshal(resp)
			if err != nil {
				err := fmt.Errorf("could not marshal unary: %w", err).Error()
				log.Println(err)
				s2c <- jsMsg{m.reqId, "err", []byte(err)}
				continue
			}

			s2c <- jsMsg{m.reqId, "ok", by}
		} else {
			s2c <- jsMsg{m.reqId, "err", []byte("unknown method:" + m.meth)}
		}
	}
}
