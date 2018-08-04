import { ExampleAction } from '../actions'
​
const exampleReducer = (state = {test: 'its not working'}, action) => {
  switch (action.type) {
    case 'DO_A_THING':
      return action.payload;
    default:
      return state
  }
}
​
export default exampleReducer;
