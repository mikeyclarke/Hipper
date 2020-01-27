import { InputRule } from 'prosemirror-inputrules';
import { EditorState, Transaction } from 'prosemirror-state';
import { MarkType, NodeType } from 'prosemirror-model';
import LinkMark from 'text-editor/Mark/Link';
import InputRuleCollectionInterface from 'text-editor/InputRuleCollection/InputRuleCollectionInterface';

function markdownLinkHandler(
    markType: MarkType,
    state: EditorState,
    match: string[],
    start: number,
    end: number
): Transaction | null {
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const [fullString, text, link, linkWww, linkPath, trailingCharacter] = match;

    if (state.doc.rangeHasMark(start, end, markType)) {
        return null;
    }

    let markDisallowed = false;
    state.doc.nodesBetween(start, end, (node) => { // eslint-disable-line consistent-return
        if (markDisallowed) {
            return false;
        }
        markDisallowed = node.marks.some(mark => mark.type.excludes(markType));
    });

    if (markDisallowed) {
        return null;
    }

    let replacement = text;
    const markEnd = start + text.length;
    if (trailingCharacter && trailingCharacter !== '') {
        replacement += trailingCharacter;
    }

    const transaction = state.tr;
    transaction.insertText(replacement, start, end);
    transaction.addMark(start, markEnd, markType.create({ href: link }));
    transaction.removeStoredMark(markType);
    return transaction;
}

function autoDetectLinkHandler(
    markType: MarkType,
    state: EditorState,
    match: string[],
    start: number,
    end: number
): Transaction | null {
    if (state.doc.rangeHasMark(start, end, markType)) {
        return null;
    }

    let markDisallowed = false;
    state.doc.nodesBetween(start, end, (node) => { // eslint-disable-line consistent-return
        if (markDisallowed) {
            return false;
        }
        markDisallowed = node.marks.some(mark => mark.type.excludes(markType));
    });

    if (markDisallowed) {
        return null;
    }

    const transaction = state.tr;
    transaction.insertText(match[0], start, end);
    transaction.addMark(start, start + match[1].length, markType.create({ href: match[1], spellcheck: 'false' }));
    transaction.removeStoredMark(markType);
    return transaction;
}

export default class Link implements InputRuleCollectionInterface {
    get requirementType(): string | null {
        return 'mark';
    }

    get requirement(): Function | null {
        return LinkMark;
    }

    public getRules(type?: MarkType | NodeType): InputRule[] {
        if (!(type instanceof MarkType)) {
            return [];
        }

        return [
            new InputRule(
                /\[(.+)\]\((https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{2,256}\.[a-zA-Z]{2,}\b([-a-zA-Z0-9@:%_+~#?&//=]*))\)(.)?$/,
                markdownLinkHandler.bind(null, type)
            ),
            new InputRule(
                /(https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{2,256}\.[a-zA-Z]{2,}\b([-a-zA-Z0-9@:%_+~#?&//=]*)).?\s$/,
                autoDetectLinkHandler.bind(null, type)
            ),
        ];
    }
}
