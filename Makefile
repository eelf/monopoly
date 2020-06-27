export GOGO_SRC = $(shell go list -f '{{.Dir}}' -m github.com/gogo/protobuf)
export PROTOC ?= protoc
export TEST ?= php -r 'exit(proc_close(proc_open("go test", [], $$_)));'

PATH := build:$(PATH)

.DEFAULT_GOAL := all

.PHONY: all run clean

default:
	@echo "your make version does not respect .DEFAULT_GOAL :( get a better make"

all: build/monopoly web/main.js

run: all
	build/monopoly --web=web/

clean:
	php client/build.php clean
	rm -fv  client/monopoly_pb.js server/monopoly.pb.go web/main.js go.sum
	rm -rfv build

build/monopoly: server/monopoly.pb.go server/cmd
	cd server/cmd && go build -o ../../build/monopoly

server/monopoly.pb.go: build/protoc-gen-gogo proto/monopoly.proto
	$(PROTOC) --gogo_out=plugins=grpc:server -Iproto -I$(GOGO_SRC) -I$(GOGO_SRC)/protobuf proto/monopoly.proto

build/protoc-gen-gogo:
	go build -o build/protoc-gen-gogo github.com/gogo/protobuf/protoc-gen-gogo

web/main.js: client/webpack.config.js client/monopoly_pb.js client
	cd client && node_modules/webpack/bin/webpack.js

client/monopoly_pb.js: proto/monopoly.proto
	$(PROTOC) --js_out=import_style=commonjs:client -Iproto proto/monopoly.proto

client/webpack.config.js:
	php client/build.php development
