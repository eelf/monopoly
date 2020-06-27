
import * as msg from './monopoly_pb';
//make generator to generate '*_wsgrpc' from proto file service descriptor
// import Monopoly from './monopoly_pb_wsgrpc';
import React from 'react';
import {render} from 'react-dom';

class MultiPromise {
    constructor(fn) {
        let res = resolution => {
            if (this.res === undefined) {
                this.resolution = resolution;
            } else {
                this.res(resolution);
            }
        };
        let rej = rejection => {
            if (this.rej === undefined) {
                this.rejection = rejection;
            } else {
                this.rej(rejection);
            }
        };

        fn(res, rej);
    }

    thenMulti(res, rej) {
        let res2, rej2;
        let p = new MultiPromise((resolve2, reject2) => {
            res2 = resolve2;
            rej2 = reject2;
        });
        if (this.resolution !== undefined) {
            return res(this.resolution);
        } else {
            this.res = resuolution => res2(res(resuolution));
        }
        if (this.rejection !== undefined) {
            return rej(this.rejection);
        } else {
            this.rej = rejection => rej2(rej(rejection));
        }
        return p;
    }
}

class WsGrpc {
    constructor(address) {
        this.ws = new WebSocket(address);
        this.id = 0;
        this.ids = {};
        this.q = [];
        this.ws.onmessage = e => {
            let id, code, text;
            text = e.data.split(':');
            [id, code] = text.splice(0, 2);
            text = text.join(':');

            console.log("message", id, code, text, text.length);

            let cb = this.ids[id];
            if (cb !== undefined) {
                console.log("decoding");
                text = new TextEncoder("utf-8").encode(text);
                console.log("decoded", text);
                if (code === 'ok') {
                    cb[0](text);
                } else {
                    cb[1](text);
                }
                // do not delete for server-streaming
                // delete this.ids[id];
            } else {
                console.log("no callbacks defined");
            }
        };

        this.ws.onopen = e => {
            for (let m of this.q) {
                this.ws.send(m);
            }
            this.q = [];
        };

        this.ws.onclose = e => {
            console.log('close', e);
        };

        this.ws.onerror = e => {
            console.log('err', e);
        };
    }
    call(meth, arg) {
        //generate id
        let id = this.id;
        this.id++;
        //serialize
        let str = id + ':' + meth + ':' + new TextDecoder("utf-8").decode(arg.serializeBinary());
        //setup response handler

        let p;
        if (meth == 'Subs') {
            p = new MultiPromise((res, rej) => {
                this.ids[id] = [res, rej];
            });
        } else {
            p = new Promise((res, rej) => {
                this.ids[id] = [res, rej];
            });
        }


        //send message
        console.log(this.ws);
        if (this.ws.readyState != 1) {
            this.q.push(str);
        } else {
            this.ws.send(str);
        }

        return p;
    }
}

//generated class
class Monopoly extends WsGrpc {
    constructor(user_id, game_id) {
        document.cookie = 'user_id=' + user_id + '; path=/';
        document.cookie = 'game_id=' + game_id + '; path=/';
        super('ws://localhost:8080/app/' + 'Monopoly');

    }
    //serverstreaming
    Subs(/*SubsRequest*/ req) {
        //SubsRespStream
        let stream;
        stream = resp => msg.SubsRespStream.deserializeBinary(resp);
        return this.call('Subs', req)
            .thenMulti(resp => msg.SubsRespStream.deserializeBinary(resp));
    }
    Chat(/*ChatRequest*/ req) {
        //ChatResponse
        return this.call('Chat', req)
            .then(resp => msg.ChatResponse.deserializeBinary(resp));
    }
}

class View2 extends React.Component {
    constructor(props) {
        super(props);
        this.state = {text:''};
    }
    componentDidMount() {
        let request = new msg.SubsRequest();
        this.props.store.Subs(request)
            .thenMulti(response => {
                console.log('response', response, response.getChatList());
                this.setState({text: this.state.text + "\n" + response.getChatList().join("\n")});
            });
    }

    render() {
        return <div>
            <div style={{whiteSpace: 'pre-line', font: '12px "Andale Mono", Menlo, Monaco, monospace'}}>{this.state.text}</div>
            <input onKeyPress={e => {
                if (e.charCode == 13) {
                    let req = new msg.ChatRequest();
                    req.setLine(e.target.value);
                    this.props.store.Chat(req)
                        .then(response => {
                            console.log('chat response', response);
                        });
                    e.target.value = '';
                }
            }} />
        </div>
    }
}

class Reg extends React.Component {
    constructor(props) {
        super(props);
        this.state = {};
    }
    render() {
        if (this.state.game_id === undefined) {
            return <div>game_id<input onKeyPress={e => {if (e.charCode == 13) this.setState({game_id:e.target.value})}} /></div>
        } if (this.state.user_id === undefined) {
            return <div>user_id<input onKeyPress={e => {if (e.charCode == 13) this.setState({user_id:e.target.value})}} /></div>
        } else {
            return <View2 store={new Monopoly(this.state.user_id, this.state.game_id)} />
        }
    }
}

render(
    <Reg />,
    document.getElementById('app'));
