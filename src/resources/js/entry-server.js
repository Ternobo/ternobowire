import app from './app';

let application = app(true, data, component).$mount();

renderVueComponentToString(application, (err, res) => {
    print(res);
});
