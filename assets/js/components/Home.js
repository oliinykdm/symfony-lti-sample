import React, {Component} from 'react';
import {Route, Routes, Link, Navigate, withRouter, BrowserRouter} from 'react-router-dom';
import NewShortMessage from './NewShortMessage';
import LatestShortMessages from './LatestShortMessages';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

class Home extends Component {
    render() {
        return (
            <div>
                <BrowserRouter>
                <nav className="navbar navbar-expand-lg navbar-dark bg-dark">
                   <Link className={"navbar-brand"} to={"/"}>Short Messages Service</Link>
                    <div className="collapse navbar-collapse" id="navbarText">
                        <ul className="navbar-nav mr-auto">
                            <li className="nav-item">
                               <Link className={"nav-link"} to={"/"}>All messages</Link>
                            </li>

                            <li className="nav-item">
                                <Link className={"nav-link"} to={"/new"}>New</Link>
                            </li>
                        </ul>
                    </div>
                </nav>

                    <Routes>
                        <Route path="/" element={<LatestShortMessages />} />
                        <Route path="/new" element={<NewShortMessage />} />
                    </Routes>
                    <ToastContainer />
                </BrowserRouter>
            </div>
        )
    }
}

export default Home;
