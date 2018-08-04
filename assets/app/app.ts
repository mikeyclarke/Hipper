require('../app/app.scss');

interface Person {
    name: string;
}

export function greeter(person: Person) {
    return "Hello, " + person.name ;
}
