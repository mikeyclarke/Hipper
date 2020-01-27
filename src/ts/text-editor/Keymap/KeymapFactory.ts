import { Plugin } from 'prosemirror-state';
import { keymap } from 'prosemirror-keymap';
import { baseKeymap } from 'prosemirror-commands';
import { Schema } from 'prosemirror-model';
import NodeInterface from 'text-editor/Node/NodeInterface';
import MarkInterface from 'text-editor/Mark/MarkInterface';
import History from 'text-editor/KeymapBinding/History';
import Strong from 'text-editor/KeymapBinding/Strong';
import Emphasis from 'text-editor/KeymapBinding/Emphasis';
import Blockquote from 'text-editor/KeymapBinding/Blockquote';
import HardBreak from 'text-editor/KeymapBinding/HardBreak';
import ListItem from 'text-editor/KeymapBinding/ListItem';
import Paragraph from 'text-editor/KeymapBinding/Paragraph';
import Heading from 'text-editor/KeymapBinding/Heading';

function isMacOsUserAgent(): boolean {
    if (navigator !== undefined) {
        return /Mac/.test(navigator.platform);
    }

    return false;
}

export default class KeymapFactory {
    public create(schema: Schema, marks: MarkInterface[], nodes: NodeInterface[]): Plugin[] {
        const availableKeymapBindings = [
            new History(),
            new Strong(),
            new Emphasis(),
            new Blockquote(),
            new HardBreak(),
            new ListItem(),
            new Paragraph(),
            new Heading(),
        ];
        const isMacOs = isMacOsUserAgent();

        const bindings = {};
        const iterations = availableKeymapBindings.length;
        for (let i = 0; i < iterations; i += 1) {
            const keymapBinding = availableKeymapBindings[i];

            if (keymapBinding.requirementType === null) {
                Object.assign(bindings, ...keymapBinding.getBindings(isMacOs));
                continue;
            }

            if (keymapBinding.requirementType === 'mark') {
                const requiredMark = marks.find(mark => {
                    return null !== keymapBinding.requirement && mark instanceof keymapBinding.requirement;
                });
                if (requiredMark) {
                    Object.assign(bindings, ...keymapBinding.getBindings(isMacOs, schema.marks[requiredMark.name]));
                }
                continue;
            }

            if (keymapBinding.requirementType === 'node') {
                const requiredNode = nodes.find(node => {
                    return null !== keymapBinding.requirement && node instanceof keymapBinding.requirement;
                });
                if (requiredNode) {
                    Object.assign(bindings, ...keymapBinding.getBindings(isMacOs, schema.nodes[requiredNode.name]));
                }
            }
        }

        return [keymap(bindings), keymap(baseKeymap)];
    }
}
