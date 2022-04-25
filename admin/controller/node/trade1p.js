const axios = require('axios');
const get = async () => {
    // axios.get('https://dashencon.com/tezt/wp-json/ds_bt/v1/tradingview/spot').then(res => {
        axios.get('https://localhost:8082/wp/ds/wp-json/ds_bt/v1/trade1p/spot').then(res => {
        console.log(res.data);
    })
}
setInterval(get, 60000)

// export NODE_TLS_REJECT_UNAUTHORIZED='0'