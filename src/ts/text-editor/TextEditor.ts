import { history } from 'prosemirror-history';
import { dropCursor } from 'prosemirror-dropcursor';
import { gapCursor } from 'prosemirror-gapcursor';
import { EditorState, Plugin } from 'prosemirror-state';
import { EditorView } from 'prosemirror-view';
import { DOMParser, Node as ProsemirrorNode, Schema } from 'prosemirror-model';
import { MarkInterface } from 'text-editor/Mark/MarkInterface';
import { MarkLoader } from 'text-editor/Mark/MarkLoader';
import { NodeInterface } from 'text-editor/Node/NodeInterface';
import { NodeLoader } from 'text-editor/Node/NodeLoader';
import { InputRulesFactory } from 'text-editor/InputRule/InputRulesFactory';
import { KeymapFactory } from 'text-editor/Keymap/KeymapFactory';
import { CommandsFactory } from 'text-editor/Command/CommandsFactory';
import { emptyDocument } from 'text-editor/Plugin/emptyDocument';
import { detachableMenu } from 'text-editor/Plugin/detachableMenu';

type InitialContent = string | object | HTMLElement;

export class TextEditor {
    private readonly defaultNodes: string[] = ['doc', 'text', 'paragraph'];
    private readonly containerElement: HTMLElement;
    private readonly marks: MarkInterface[];
    private readonly nodes: NodeInterface[];
    private readonly schema: Schema;
    private readonly inputRules: Plugin;
    private readonly commands: Record<string, object>;
    private readonly keymaps: Plugin[];
    private readonly plugins: Plugin[];
    private readonly content: ProsemirrorNode;
    private readonly state: EditorState;
    private readonly view: EditorView;

    constructor(
        containerElement: HTMLElement,
        initialContent: InitialContent,
        allowedMarks: string[],
        allowedNodes: string[],
        userAgentProfile: Record<string, any> | null,
    ) {
        this.containerElement = containerElement;

        this.marks = this.createMarks(allowedMarks);
        this.nodes = this.createNodes(allowedNodes);
        this.schema = this.createSchema();
        this.inputRules = this.createInputRules();
        this.commands = this.createCommands();
        this.keymaps = this.createKeymaps();
        this.plugins = this.createPlugins(userAgentProfile);
        this.content = this.createContent(initialContent);
        this.state = this.createState();
        this.view = this.createView();
    }

    public getContent(): object {
        return this.view.state.doc.toJSON();
    }

    private createNodes(allowedNodes: string[]): NodeInterface[] {
        const nodeLoader = new NodeLoader();
        const nodes = nodeLoader.getAllWithNames([...this.defaultNodes, ...allowedNodes]);
        return nodes;
    }

    private createMarks(allowedMarks: string[]): MarkInterface[] {
        const markLoader = new MarkLoader();
        const marks = markLoader.getAllWithNames(allowedMarks);
        return marks;
    }

    private createSchema(): Schema {
        const marksObj: Record<string, object> = {};
        const nodesObj: Record<string, object> = {};
        return new Schema({
            marks: this.marks.reduce((accumulator, mark) => {
                accumulator[mark.name] = mark.spec;
                return accumulator;
            }, marksObj),
            nodes: this.nodes.reduce((accumulator, node) => {
                accumulator[node.name] = node.spec;
                return accumulator;
            }, nodesObj),
        });
    }

    private createInputRules(): Plugin {
        const factory = new InputRulesFactory();
        const inputRules = factory.create(this.schema, this.marks, this.nodes);
        return inputRules;
    }

    private createCommands(): Record<string, object> {
        const factory = new CommandsFactory();
        const commands = factory.create(this.schema, this.marks, this.nodes);
        return commands;
    }

    private createKeymaps(): Plugin[] {
        const factory = new KeymapFactory();
        const keymaps = factory.create(this.schema, this.marks, this.nodes);
        return keymaps;
    }

    private createPlugins(userAgentProfile: Record<string, any> | null): Plugin[] {
        const menuPluginOptions = {
            detachable: false,
            isDetached: false,
            patchMobileSafari: false,
        };

        if (userAgentProfile !== null &&
            userAgentProfile.is_mobile_browser === false &&
            !looksLikeiPadSafari(userAgentProfile)
        ) {
            menuPluginOptions.detachable = true;
            menuPluginOptions.isDetached = true;
        }

        if (userAgentProfile !== null && userAgentProfile.is_ios === true) {
            menuPluginOptions.patchMobileSafari = true;
        }

        const plugins = [
            dropCursor(),
            gapCursor(),
            history(),
            emptyDocument(),
            detachableMenu(this.commands, menuPluginOptions),
        ];
        return plugins;
    }

    private createContent(content: InitialContent): ProsemirrorNode {
        if (content instanceof HTMLElement) {
            return DOMParser.fromSchema(this.schema).parse(content);
        }

        if (content instanceof Object) {
            return this.schema.nodeFromJSON(content);
        }

        const element = document.createElement('div');
        element.innerHTML = content;
        return DOMParser.fromSchema(this.schema).parse(element);
    }

    private createState(): EditorState {
        return EditorState.create({
            doc: this.content,
            schema: this.schema,
            plugins: [this.inputRules, ...this.keymaps, ...this.plugins],
        });
    }

    private createView(): EditorView {
        return new EditorView(this.containerElement, {
            state: this.state,
        });
    }
}

function looksLikeiPadSafari(userAgentProfile: Record<string, any>): boolean {
    return userAgentProfile.is_ipad_or_macos_safari === true && 'TouchEvent' in window;
}
