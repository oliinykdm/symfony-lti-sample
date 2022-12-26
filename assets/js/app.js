// ./src/js/app.js

import React from 'react';
import ReactDOM from "react-dom/client";
import { BrowserRouter as Router, useHistory } from 'react-router-dom';
import '../css/app.css';
import Home from './components/Home';
import reportWebVitals from './reportWebVitals';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

const root = ReactDOM.createRoot(document.getElementById("root"));
root.render(
    <React.StrictMode>
        <Home />
    </React.StrictMode>
);

reportWebVitals();