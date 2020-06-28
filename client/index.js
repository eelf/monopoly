
import * as msg from './monopoly_pb';
//make generator to generate '*_wsgrpc' from proto file service descriptor
// import Monopoly from './monopoly_pb_wsgrpc';
import React from 'react';
import {render} from 'react-dom';

class MultiPromise {
    static get inrej() {return this._inrej || false;}
    static set inrej(v) {this._inrej = v;}
    static swapInrej(v) {
        let old = this.inrej;
        this.inrej = v;
        return old;
    }

    constructor(fn) {
        //this.res this.rej - reference to child, it may not be installed yet
        let res = resolution => {
            if (this.res === undefined) {
                this.resolution = resolution;
            } else {
                try {
                    this.res(resolution);
                } catch (e) {
                    // console.log('mp.res.e', e);
                    throw e;
                }
            }
        };
        let rej = rejection => {
            // console.log('mp.rej', rejection, this.rej, this.rejection, this.constructor.inrej);

            let old_in_rej = this.constructor.swapInrej(true);

            // console.log('mp.rej old_in_rej', old_in_rej);

            if (this.rej === undefined) {
                if (old_in_rej) {
                    //too late to install
                    throw rejection;//todo maybe custom exception: multipromise not handled
                }
                this.rejection = rejection;
            } else {
                try {
                    // console.log('mp.rej calling');
                    let rej_result = this.rej(rejection);
                    // console.log('mp.rej result', rej_result);
                } catch (e) {
                    // console.log('mp.rej exception', e);
                    throw e;
                }
            }
        };

        try {
            fn(res, rej);
        } catch (e) {
            rej(e);
        }
    }

    then(res, rej) {
        //this - parent, new MultiPromise - child, resolution/rejection should propagate from parent to child
        let res2, rej2;
        let p = new MultiPromise((resolve2, reject2) => {
            res2 = resolve2;
            rej2 = reject2;
        });
        if (this.resolution !== undefined) {
            return res(this.resolution);
        } else {
            this.res = resuolution => {
                try {
                    return res2(res(resuolution))
                } catch (e) {
                    // console.log('mp.then.res.e', e);
                    let rej1 = rej2(e);
                    // console.log('mp.then.res.rej1', rej1, rej2, rej);
                    return rej1;
                }
            };
        }
        if (rej !== undefined) {
            if (this.rejection !== undefined) {
                return rej(this.rejection);
            } else {
                this.rej = rejection => rej2(rej(rejection));
            }
        } else {
            // console.log('mp.then default rej', rej, this.rejection, rej2);
            if (this.rejection !== undefined) {
                //todo dunno
                throw this.rejection;
            } else {
                //todo dunno if default rej fun is needed
                this.rej = rejection => {
                    // console.log('mp.then.default rej.rej', rejection, rej2);
                    return rej2(rejection);
                }
            }
        }
        return p;
    }
    catch(rej) {
        let res2, rej2;
        let p = new MultiPromise((resolve2, reject2) => {
            res2 = resolve2;
            rej2 = reject2;
        });
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

            let cb = this.ids[id];

            console.log('onmessage', id, code, text.length, text, cb);

            if (cb !== undefined) {
                if (code === 'ok') {
                    cb[0](new TextEncoder("utf-8").encode(text));
                } else {
                    cb[1](text);
                }
                // do not delete for server-streaming
                // delete this.ids[id];
            } else {
                console.error("no callbacks defined");
            }
        };

        this.ws.onopen = e => {
            console.log('open', e);
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
        let p = new MultiPromise((res, rej) => {
            let lowestRej = rejection => {
                console.log('lowest rejection', rejection);
                //todo dunno if return is needed
                return rej(rejection);
            };
            this.ids[id] = [res, lowestRej];
        });

        //send message
        if (this.ws.readyState !== 1) {
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
        return this.call('Subs', req)
            .then(resp => {
                console.log('Monopoly.Subs.res', resp);
                let deserializeBinary = msg.SubsRespStream.deserializeBinary(resp);
                console.log('Monopoly.Subs.deser', deserializeBinary);
                return deserializeBinary;
            });
    }
    Chat(/*ChatRequest*/ req) {
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
            .then(response => {
                console.log('response', response, response.getChatList());
                this.setState({text: this.state.text + "\n" + response.getChatList().join("\n")});
            });
    }

    render() {
        let a = 50;
        let b = Math.ceil(a / 1.618);
        let all = {
            border: '1px solid #333',
        };
        let corner = Object.assign(all, {
            display: 'block',
            bakcgroundColor: 'royalblue',
            width: a + 'px',
            height: a + 'px',
        });
        let hor = Object.assign(all, {
            width: b + 'px',
            height: a + 'px',
        });
        let vert = Object.assign(all, {
            height: b + 'px',
        });
        return <div>
            <table>
                <tbody>
                <tr>
                    <td style={corner}>Free</td>
                    <td style={hor}>M51</td>
                    <td style={hor}>Ch2</td>
                    <td style={hor}>M52</td>
                    <td style={hor}>M53</td>
                    <td style={hor}>Quadro3</td>
                    <td style={hor}>M61</td>
                    <td style={hor}>M62</td>
                    <td style={hor}>Duo2</td>
                    <td style={hor}>M63</td>
                    <td style={corner}>GoJail</td>
                </tr>
                <tr><td style={vert}>M43</td>    <td colSpan="9">&nbsp;</td> <td style={vert}>M71</td></tr>
                <tr><td style={vert}>M42</td>    <td colSpan="9">&nbsp;</td> <td style={vert}>M72</td></tr>
                <tr><td style={vert}>Cc2</td>    <td colSpan="9">&nbsp;</td> <td style={vert}>Cc3</td></tr>
                <tr><td style={vert}>M41</td>    <td colSpan="9">&nbsp;</td> <td style={vert}>M73</td></tr>
                <tr><td style={vert}>Quadro2</td><td colSpan="9">&nbsp;</td> <td style={vert}>Quadro4</td></tr>
                <tr><td style={vert}>M33</td>    <td colSpan="9">&nbsp;</td> <td style={vert}>Ch3</td></tr>
                <tr><td style={vert}>M32</td>    <td colSpan="9">&nbsp;</td> <td style={vert}>M81</td></tr>
                <tr><td style={vert}>Duo1</td>   <td colSpan="9">&nbsp;</td> <td style={vert}>LuxTax</td></tr>
                <tr><td style={vert}>M31</td>    <td colSpan="9">&nbsp;</td> <td style={vert}>M82</td></tr>
                <tr>
                    <td style={corner}>Jail</td>
                    <td style={hor}>M23</td>
                    <td style={hor}>M22</td>
                    <td style={hor}>Ch1</td>
                    <td style={hor}>M21</td>
                    <td style={hor}>Quadro1</td>
                    <td style={hor}>IncTax</td>
                    <td style={hor}>M12</td>
                    <td style={hor}>Cc1</td>
                    <td style={hor}>M11</td>
                    <td style={corner}>Go</td>
                </tr>
                </tbody>
            </table>
            <div style={{whiteSpace: 'pre-line', font: '12px "Andale Mono", Menlo, Monaco, monospace'}}>{this.state.text}</div>
            <input onKeyPress={e => {
                if (e.charCode === 13) {
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
    static get link() {
        return {
            color: 'royalblue',
            textDecoration: 'underline',
            textDecorationStyle: 'dotted',
            cursor: 'pointer',
        };
    }
    constructor(props) {
        super(props);
        this.state = {};
    }
    render() {
        if (this.state.game_id === undefined) {
            return <ul>
                <li style={this.constructor.link} onClick={() => this.setState({game_id:123, user_id:456})}>game:1 user:Crash Override</li>
                <li style={this.constructor.link} onClick={() => this.setState({game_id:123, user_id:457})}>game:1 user:Acid Burn</li>
            </ul>
        } else {
            return <View2 store={new Monopoly(this.state.user_id, this.state.game_id)} />
        }
    }
}

render(
    <Reg />,
    document.getElementById('app'));
