require('./bootstrap');

require('moment');

import Vue from 'vue';
import TernoboApp from "./Application.vue";
import { store } from './TernoboWire/TernoboWire';
import { plugin } from "./TernoboWire/TernoboWire";

export default function (ssr = false, data = null, component = null) {
    let appInstance = null;
    Vue.use(plugin);
    let vuexStore = store();

    if (ssr) {
        appInstance = new Vue({
            store: vuexStore,
            render: (h) =>
                h(TernoboApp, {
                    props: {
                        initialData: data,
                        initialComponent: component,
                        resolveComponent: (component) => import(`./Pages/${component}`),
                    },
                }),
        });
    } else {
        let instanceData = window.ternoboApplicationData;
        component = instanceData.component;

        appInstance = new Vue({
            store: vuexStore,
            render: (h) =>
                h(TernoboApp, {
                    props: {
                        initialData: instanceData.data,
                        initialComponent: component,
                        resolveComponent: (component) => import(`./Pages/${component}`),
                    },
                }),
        });
    }
    return appInstance;
};
