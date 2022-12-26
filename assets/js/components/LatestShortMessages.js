import React, {Component} from 'react';
import Moment from 'react-moment';
import 'moment-timezone';
import axios from 'axios';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

class LatestShortMessages extends Component {
    constructor() {
        super();

        this.state = { short_messages: [], loading: true}
    }

    componentDidMount() {
        this.getShortMessages();
    }
    getShortMessages() {
        axios.get('/api/shortmessages').then(res => {
            const short_messages = res.data.slice(0,15);
            this.setState({ short_messages, loading: false })
        })
    }
    deleteShortMessage(uuid) {
        let isExecuted = confirm("Are you sure to execute this action?");
        if(isExecuted) {
            axios.delete('/api/shortmessages', { params: { uuid: uuid } }).then(res => {
                this.getShortMessages();
                toast.success('Success! The message has successfully deleted!', {
                    position: "top-center",
                    autoClose: 5000,
                    hideProgressBar: false,
                    closeOnClick: true,
                    pauseOnHover: true,
                    draggable: true,
                    progress: undefined,
                });
            })
        }

    }
    render() {
        const loading = this.state.loading;
        return (
            <div>
                <div>
                    <section className="row-section">
                        <div className="container">
                            <div className="row">
                                <h2 className="text-center"><span>List of the latest short messages</span></h2>
                            </div>
                {loading ? (
                    <div className={'row text-center'}>
                        <span className="fa fa-spin fa-spinner fa-4x"></span>
                    </div>
                ) : (
                    <div className={'row'}>
                        {this.state.short_messages.map(message =>
                            <div className="col-md-10 offset-md-1 row-block" key={message.id}>
                                <ul id="sortable">
                                    <li>
                                        <div className="media">
                                            <div className="media-body">
                                              <h4>{message.messageText}</h4>
                                                <p></p>
                                               <p><b>Author: </b>
                                                 {message.messageAuthor === 1
                                                    ? <i>Anonymous</i>
                                                    : <i>{message.messageAuthor}</i>
                                                }
                                               </p>
                                                <p><b>UUID: </b>
                                                    {message.uuid}</p>
                                                <p><b>Date: </b>
                                                    <Moment fromNow date={message.messageDate} />
                                                    </p>
                                                <p><button type="button" className="btn btn-outline-danger" onClick={this.deleteShortMessage.bind(this, message.uuid)}>Delete</button></p>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        )}
                    </div>
                )}
                        </div>
                    </section>
                </div>
            </div>
        )
    }
}

export default LatestShortMessages;
