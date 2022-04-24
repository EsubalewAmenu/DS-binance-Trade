const axios = require('axios');
const get = async () => {
    // axios.get('https://dashencon.com/tezt/wp-json/ds_bt/v1/tradingview/spot').then(res => {
        axios.get('https://localhost:8082/wp/ds/wp-json/ds_bt/v1/holderv2/spot').then(res => {
        console.log(res.data);
    })
}
setInterval(get, 60000)

// to fix "Error: self signed certificate", run "export NODE_TLS_REJECT_UNAUTHORIZED='0'"

// to fix "Error: Cannot find module 'axios'", run "npm install axios"