
import { render } from 'react-dom';
import React from 'react';
import { Provider } from 'react-redux';
import { createStore } from 'redux';

import InvoiceForm from './containers/InvoiceForm';
import reducer from './reducer';

const store = createStore(reducer, window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__());

render(<Provider store={store}><InvoiceForm /></Provider>, document.getElementById('invoice'));