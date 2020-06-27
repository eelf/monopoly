export GOGO_SRC = $(shell go list -f '{{.Dir}}' -m github.com/gogo/protobuf)
export PROTOC ?= protoc
export TEST ?= php -r 'exit(proc_close(proc_open("go test", [], $$_)));'

PATH := build:$(PATH)

.DEFAULT_GOAL := all

.PHONY: server client

default:
	@echo "your make version does not respect .DEFAULT_GOAL :( get a better make"

build/protoc-gen-gogo:
	go build -o build/protoc-gen-gogo github.com/gogo/protobuf/protoc-gen-gogo

all: build/protoc-gen-gogo server client

server:
	$(PROTOC) --gogo_out=plugins=grpc:server -Iproto -I$(GOGO_SRC) -I$(GOGO_SRC)/protobuf proto/monopoly.proto
	cd server/cmd && go build -o ../../build/monopoly

client: client/webpack.config.js
	$(PROTOC) --js_out=import_style=commonjs:client -Iproto proto/monopoly.proto
	cd client && node_modules/webpack/bin/webpack.js

client/webpack.config.js:
	php client/build.php development

run: all
	build/monopoly --web=web/
