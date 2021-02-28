require('./bootstrap');

require('moment');

import Vue from 'vue';
import {
    plugin,
} from 'wire-js';


export default function (ssr = false, dataToken = null, component = null) {
    let appInstance = null;
    Vue.use(plugin);
    let vuexStore = store();

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
