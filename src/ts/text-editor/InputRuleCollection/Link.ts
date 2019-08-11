import { InputRule } from 'prosemirror-inputrules';
import { EditorState, Transaction } from 'prosemirror-state';
import { MarkType, NodeType } from 'prosemirror-model';
import { Link as LinkMark } from 'text-editor/Mark/Link';
import { InputRuleCollectionInterface } from 'text-editor/InputRuleCollection/InputRuleCollectionInterface';

export class Link implements InputRuleCollectionInterface {
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
                /(https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{2,256}\.[a-zA-Z]{2,}\b([-a-zA-Z0-9@:%_+~#?&//=]*)).?\s$/,
                autoDetectLinkHandler.bind(null, type)
            ),
            new InputRule(
                /\[(.+)\]\((https?:\/\/(www\.)?[-a-zA-Z0-9@:%._+~#=]{2,256}\.[a-zA-Z]{2,}\b([-a-zA-Z0-9@:%_+~#?&//=]*))\)$/,
                markdownLinkHandler.bind(null, type)
            ),
        ];
    }
}

function markdownLinkHandler(
    markType: MarkType,
    state: EditorState,
    match: string[],
    start: number,
    end: number
): Transaction | null {
    if (state.doc.rangeHasMark(start, end, markType)) {
        return null;
    }
    const transaction = state.tr;
    transaction.insertText(match[1], start, end);
    transaction.addMark(start, start + match[1].length, markType.create({ href: match[2] }));
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
    const transaction = state.tr;
    transaction.insertText(match[0], start, end);
    transaction.addMark(start, start + match[1].length, markType.create({ href: match[1], spellcheck: 'false' }));
    transaction.removeStoredMark(markType);
    return transaction;
}
