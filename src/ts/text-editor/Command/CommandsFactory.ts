import { Schema } from 'prosemirror-model';
import MarkInterface from 'text-editor/Mark/MarkInterface';
import NodeInterface from 'text-editor/Node/NodeInterface';
import Strong from 'text-editor/Command/Strong';
import Emphasis from 'text-editor/Command/Emphasis';
import Strike from 'text-editor/Command/Strike';
import Blockquote from 'text-editor/Command/Blockquote';
import CodeBlock from 'text-editor/Command/CodeBlock';
import OrderedList from 'text-editor/Command/OrderedList';
import UnorderedList from 'text-editor/Command/UnorderedList';
import Heading from 'text-editor/Command/Heading';

export default class CommandsFactory {
    public create(schema: Schema, marks: MarkInterface[], nodes: NodeInterface[]): Record<string, object> {
        const availableCommands = [
            new Strong(),
            new Emphasis(),
            new Strike(),
            new Blockquote(),
            new CodeBlock(),
            new OrderedList(),
            new UnorderedList(),
            new Heading(),
        ];

        const commands: Record<string, object> = {};

        const iterations = availableCommands.length;
        for (let i = 0; i < iterations; i += 1) {
            const command = availableCommands[i];

            if (command.requirementType === null) {
                commands[command.name] = {
                    label: command.label,
                    getCommand: command.getCommand,
                };
                continue;
            }

            if (command.requirementType === 'mark') {
                const requiredMark = marks.find(mark => {
                    return null !== command.requirement && mark instanceof command.requirement;
                });
                if (requiredMark) {
                    commands[command.name] = {
                        label: command.label,
                        getCommand: command.getCommand.bind(null, schema.marks[requiredMark.name]),
                    };
                }
                continue;
            }

            if (command.requirementType === 'node') {
                const requiredNode = nodes.find(node => {
                    return null !== command.requirement && node instanceof command.requirement;
                });
                if (requiredNode) {
                    commands[command.name] = {
                        label: command.label,
                        getCommand: command.getCommand.bind(null, schema.nodes[requiredNode.name]),
                    };
                }
            }
        }

        return commands;
    }
}
