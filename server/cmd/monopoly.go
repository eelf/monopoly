package main

import (
	"context"
	"fmt"
	flag "go.badoo.dev/core/pflag"
	"google.golang.org/grpc"
	"google.golang.org/grpc/metadata"
	"google.golang.org/grpc/peer"
	monopoly "kek/server"
	"log"
	"net"
	"sync"
)

type state uint32
const (
	Waiting state = iota
	Rolling
	Trading
)

type userIdPri string
type userIdPub uint32

type user struct {
	pubId userIdPub
	name string
	st state
	trading string
	streams sync.Map//monopoly.Monopoly_SubsServer
}

type game struct {
	lock sync.Mutex
	users map[userIdPri]*user
	turn userIdPri //which user's turn
	//chat []string
	locs map[userIdPri]monopoly.Cell
}

type MyMonopolyServer struct {
	games map[string]*game
}

func (m *MyMonopolyServer) Chat(ctx context.Context, req *monopoly.ChatRequest) (*monopoly.ChatResponse, error) {
	var userId userIdPri
	var gameId string
	var err error
	if userId, gameId, err = authorize(ctx); err != nil {
		return nil, err
	}
	game, ok := m.games[gameId]
	if !ok {
		return nil, fmt.Errorf("no such game")
	}
	user, ok := game.users[userId]
	if !ok {
		return nil, fmt.Errorf("no such user")
	}
	req.GetLine()

	resp := &monopoly.SubsRespStream{Chat: []string{user.name + ": " + req.GetLine()}}

	for _, u := range game.users {
		u.streams.Range(func(key, value interface{}) bool {
			err := value.(monopoly.Monopoly_SubsServer).Send(resp)
			log.Println("send chat", err)
			return true
		})
	}

	return &monopoly.ChatResponse{}, nil
}

func authorize(ctx context.Context) (userId userIdPri, gameId string, err error) {
	md, ok := metadata.FromIncomingContext(ctx)
	if !ok {
		err = fmt.Errorf("no md")
		return
	}
	userIds := md.Get("user_id")
	if len(userIds) != 1 {
		err = fmt.Errorf("user_id wrong len")
		return
	}
	userId = userIdPri(userIds[0])

	gameIds := md.Get("game_id")
	if len(gameIds) != 1 {
		err = fmt.Errorf("game_id wrong len")
		return
	}
	gameId = gameIds[0]
	log.Println("user", userId, "game", gameId)
	return
}

func (m *MyMonopolyServer) Subs(req *monopoly.SubsRequest, oStr monopoly.Monopoly_SubsServer) error {
	var userId userIdPri
	var gameId string
	var err error
	ctx := oStr.Context()

	log.Println("Subs ctx", ctx)

	p, ok := peer.FromContext(ctx)
	if !ok {
		return fmt.Errorf("could not get peer from context")
	}
	log.Println("peerKey", p)

	//sts := grpc.ServerTransportStreamFromContext(ctx)
	//log.Println(sts.Method())

	if userId, gameId, err = authorize(ctx); err != nil {
		return err
	}

	game, ok := m.games[gameId]
	if !ok {
		return fmt.Errorf("no such game")
	}
	user, ok := game.users[userId]
	if !ok {
		return fmt.Errorf("no such user")
	}

	resp := &monopoly.SubsRespStream{Chat: []string{user.name + ": connected"}}

	for _, u := range game.users {
		u.streams.Range(func(key, value interface{}) bool {
			err := value.(monopoly.Monopoly_SubsServer).Send(resp)
			log.Println("Subs before waiting, connect broadcast", err)
			return true
		})
	}


	user.streams.Store(p.Addr, oStr)

	game.lock.Lock()
	var playersLoc []*monopoly.SubsRespStream_PlayerLoc
	for upri, loc := range game.locs {
		playersLoc = append(playersLoc, &monopoly.SubsRespStream_PlayerLoc{
			Id:                   uint32(game.users[upri].pubId),
			Cell:                 loc,
			Name:                 game.users[upri].name,
		})
	}
	resp = &monopoly.SubsRespStream{
		Locs: playersLoc,
		Turn: uint32(game.users[game.turn].pubId),}
	err = oStr.Send(resp)
	if err != nil {
		log.Println("send gamesstate", err)
		return err
	}
	game.lock.Unlock()

	log.Println("Subs streaming call: waiting ctx.Done")
	<- ctx.Done()
	log.Println("Subs streaming call: waited ctx.Done")

	user.streams.Delete(p.Addr)

	resp = &monopoly.SubsRespStream{Chat: []string{user.name + ": disconnected"}}
	for _, u := range game.users {
		u.streams.Range(func(key, value interface{}) bool {
			err := value.(monopoly.Monopoly_SubsServer).Send(resp)
			log.Println("Subs done, disconnect broadcast", err)
			return true
		})
	}

	return nil
}

func (m *MyMonopolyServer) RollDice(context.Context, *monopoly.RollDiceRequest) (*monopoly.RollDiceResponse, error) {
	panic("implement me")
}



func main() {
	s := grpc.NewServer()

	m := &MyMonopolyServer{}
	m.games = make(map[string]*game)

	user1pri := userIdPri("456")
	user1 := &user{
		pubId:   1488,
		name:    "Crash Override",
	}

	user2pri := userIdPri("457")
	user2 := &user{
		pubId:   1489,
		name:    "Acid Burn",
	}

	m.games["123"] = &game{locs:  make(map[userIdPri]monopoly.Cell), users: make(map[userIdPri]*user)}
	m.games["123"].locs[user1pri] = monopoly.Cell_Go
	m.games["123"].locs[user2pri] = monopoly.Cell_Go
	m.games["123"].users[user1pri] = user1
	m.games["123"].users[user2pri] = user2
	m.games["123"].turn = user1pri

	monopoly.RegisterMonopolyServer(s, m)

	dir := flag.String("web", "", "web root")
	flag.Parse()


	//go mitm("0.0.0.0:8088", "localhost:8081")

	go jsWebsocketProxy(*dir, "0.0.0.0:8080", "localhost:8081")

	sock, err := net.Listen("tcp", "0.0.0.0:8081")
	if err != nil {
		log.Fatalln("svc listen", err)
	}

	err = s.Serve(sock)
	log.Fatalln("svc serve", err)
}
