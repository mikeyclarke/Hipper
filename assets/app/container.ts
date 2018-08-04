import * as Bottle from 'bottlejs';

const bottle = new Bottle();

class HelloWorld {
    private name : string;
    constructor(name? : string) {
        this.name = name;
    }

    sayHello() {
        const message = `hello ${this.name ? this.name : 'DI'}`;
        console.log(message);
    }
}

bottle.provider('helloWorld', function (container) {
    const composition = true;
    let args = null;
    if (composition === true) {
        args = 'matt';
    }
    this.$get = function (container) {
        return new HelloWorld(args);
    };
});

export default bottle.container;
