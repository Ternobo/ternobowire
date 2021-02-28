import app from './app';

let application = app(true, dataToken, component);

renderVueComponentToString(application, (err, res) => {
    print(res);
});
