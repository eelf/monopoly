package main

import (
	"encoding/hex"
	"io"
	"log"
	"net"
	"os"
	"sync"
)

type ml struct {
	w io.Writer
	p string
}

var mlLock sync.Mutex

func (m *ml) Write(p []byte) (n int, err error) {
	mlLock.Lock()
	defer mlLock.Unlock()
	log.Println(m.p)
	return m.w.Write(p)
}

func mitm(from, to string) {
	prx, err := net.Listen("tcp", from)
	if err != nil {
		panic(err)
	}

	for {
		c, err := prx.Accept()
		if err != nil {
			panic(err)
		}

		outc, err := net.Dial("tcp", to)
		if err != nil {
			panic(err)
		}
		go func(s, d net.Conn) {
			defer s.Close()
			defer d.Close()


			dd := io.MultiWriter(d, &ml{hex.Dumper(os.Stdout), "ws2grpc"})
			go func() {
				written, err := io.Copy(dd, s)
				log.Println(written, err)
			}()

			sd := io.MultiWriter(s, &ml{hex.Dumper(os.Stdout), "grpc2ws"})
			written, err := io.Copy(sd, d)
			log.Println(written, err)
		}(c, outc)

	}
}
