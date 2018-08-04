require('../app/app.scss');
import test from '../app/Library/test';

interface Person {
    name: string;
}

export function greeter(person: Person) {
    return test(person.name);
}

document.write(greeter({name: 'matt'}));
