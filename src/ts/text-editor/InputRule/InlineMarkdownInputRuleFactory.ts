import { EditorState, Transaction } from 'prosemirror-state';
import { InputRule } from 'prosemirror-inputrules';
import { MarkType } from 'prosemirror-model';

function ruleHandler(
    markType: MarkType,
    wrappingCharacterOccurances: number = 1,
    state: EditorState,
    match: string[],
    start: number,
    end: number
): Transaction | null {
    let replacement = match[2];

    // The replacement will overwrite the matched text hence shortening the range. Remember that the last character in
    // the pattern isn’t actually in the DOM, we will put it there during the replacement, hence we only need to
    // account for the removal of the opening asterisk/underscore from the range, not the closing asterisk/underscore.
    // E.g. “*foo*” is our match, “*foo” is what’s in the DOM, and “foo” is our replacement, so subtract 1 character.
    let markEnd = end - ((wrappingCharacterOccurances * 2) - 1);

    // If preceeding space, skip over it
    if (match[1] && match[1] !== '') {
        start += 1; // eslint-disable-line no-param-reassign
    }

    // If trailing character add it to the replacement so that it’s added to the DOM and subtract 1 character from the
    // range to be used to create the mark so that the mark doesn’t include the trailing character.
    if (match[3] && match[3] !== '') {
        replacement += match[3];
        markEnd -= 1;
    }

    let markDisallowed = false;
    state.doc.nodesBetween(start, end, node => { // eslint-disable-line consistent-return
        if (markDisallowed) {
            return false;
        }
        markDisallowed = node.marks.some(mark => mark.type.excludes(markType));
    });

    if (markDisallowed) {
        return null;
    }

    const transaction = state.tr;
    transaction.insertText(replacement, start, end);
    transaction.addMark(start, markEnd, markType.create());
    transaction.removeStoredMark(markType);
    return transaction;
}

export default class InlineMarkdownInputRuleFactory {
    public create(
        markType: MarkType,
        wrappingCharacter: string,
        wrappingCharacterOccurances: number = 1 // Consecutive occurances in pattern, e.g. _italic_ = 1, **bold** = 2
    ): InputRule {
        if (wrappingCharacter.length !== 1) {
            throw new Error('`wrappingCharacter` must be a single character');
        }

        return new InputRule(
            this.composeRegularExpression(wrappingCharacter, wrappingCharacterOccurances),
            ruleHandler.bind(null, markType, wrappingCharacterOccurances)
        );
    }

    private composeRegularExpression(
        wrappingCharacter: string,
        wrappingCharacterOccurances: number
    ): RegExp {
        const expr = [
            '(^|\\s)[',
            wrappingCharacter,
            ']{',
            wrappingCharacterOccurances,
            '}([^',
            wrappingCharacter,
            ']+)[',
            wrappingCharacter,
            ']{',
            wrappingCharacterOccurances,
            '}(.)?$',
        ].join('');

        return new RegExp(expr);
    }
}
