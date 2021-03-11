require('./bootstrap');

require('moment');

import Vue from 'vue';
import TernoboApp from "wire-js";
import {
    plugin,
} from 'wire-js';

Vue.use(plugin);

export default function (ssr = false, dataToken = null, component = null, vuexStore = store()) {
    let appInstance = null;
    if (ssr) {
        appInstance = new Vue({
            store: vuexStore,
            render: (h) =>
                h(TernoboApp, {
                    props: {
                        dataToken: dataToken,
                        resolveComponent: (component) => import(`./Pages/${component}`),
                    },
                }),
        });
    } else {
        let dataToken = document.body.dataset.wire;
        document.body.dataset.wire = "";
        appInstance = new Vue({
            store: vuexStore,
            render: (h) =>
                h(TernoboApp, {
                    props: {
                        dataToken: dataToken,
                        resolveComponent: (component) => import(`./Pages/${component}`),
                    },
                }),
        });
    }
    return appInstance;
};
