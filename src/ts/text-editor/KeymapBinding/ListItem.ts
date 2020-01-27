import { splitListItem, liftListItem, sinkListItem } from 'prosemirror-schema-list';
import { MarkType, NodeType } from 'prosemirror-model';
import ListItemNode from 'text-editor/Node/ListItem';
import KeymapBindingInterface from 'text-editor/KeymapBinding/KeymapBindingInterface';

export default class ListItem implements KeymapBindingInterface {
    get requirementType(): string | null {
        return 'node';
    }

    get requirement(): Function | null {
        return ListItemNode;
    }

    public getBindings(isMacOs: boolean, type?: MarkType | NodeType): object[] {
        if (!(type instanceof NodeType)) {
            return [];
        }

        return [
            { Enter: splitListItem(type) },
            { 'Mod-[': liftListItem(type) },
            { 'Mod-]': sinkListItem(type) },
            { Tab: sinkListItem(type) },
            { 'Shift-Tab': liftListItem(type) },
        ];
    }
}
