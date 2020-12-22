import WireLink from "../Components/WireLink";
import Vuex from 'vuex'

class TernoboWire {
    constructor(application, data) {
        this.app = application;
        this.data = data;
        window.history.replaceState({ visitId: "wire" }, "", window.location.pathname + window.location.hash);
        window.addEventListener('popstate', (event) => {
            if (JSON.stringify(window.history.state) == JSON.stringify(this.createVisitId())) {
                this.visit(window.location.pathname + window.location.hash, {}, 'get', false);
            }
        })
    }

    visit(location, data = {}, type = 'get', pushState = true) {
        let onStart = new CustomEvent('ternobo:navigate', { detail: { location: location } });
        window.dispatchEvent(onStart);
        axios({
            method: type,
            data: data,
            url: location,
            headers: {
                "X-TernoboWire": true
            }
        }).then((response) => {
            if (response.headers['x-ternobowire']) {
                this.loadComponent(response.data.component, response.data.data);
                if (pushState) {
                    window.history.pushState(this.createVisitId(), "", location);
                }
                const onLoaded = new CustomEvent('ternobo:loaded', { detail: { location: location } });
                window.dispatchEvent(onLoaded);
            } else {
                window.location = location;
            }
        }).catch((err) => {
            if (!TernoboWire.production) {
                console.log(err);
            }
        });
    }
    loadComponent(component, data) {
        this.app.component = component;
        this.app.data = data;
        this.app.updateComponent();
    }
    createVisitId() {
        this.visitId = { visitId: "wire" }
        return this.visitId
    }
}
export const plugin = {
    install(Vue) {
        Vue.use(Vuex);
        Vue.component("wire-link", WireLink);
    }
}

export function store(options = { states: {}, getters: {}, mutations: {} }) {
    if (options.states) {
        options.states.user = null;
        options.states.ternoboWireApp = null;
    } else {
        options.states = {
            user: null,
            ternoboWireApp: null
        };
    }
    if (options.mutations) {
        options.mutations.setupApp = function (state, payload) {
            state.ternoboWireApp = new TernoboWire(payload.app, payload.data);
        };

        options.mutations.userUpdate = function (state) {
            axios.post('/ternobo-wire/get-user').then((response) => {
                state.user = response.data.user;
            });
        };
    } else {
        options.mutations = {
            userUpdate(state) {
                axios.post('/ternobo-wire/get-user').then((response) => {
                    state.user = response.data.user;
                });
            },
            setupApp(state, payload) {
                state.ternoboWireApp = new TernoboWire(payload.app, payload.data);
            }
        };
    }
    return new Vuex.Store(options);
};
