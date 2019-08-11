import { MarkType, NodeType } from 'prosemirror-model';

export type CommandRequirementType = 'mark' | 'node' | null;
export type GetCommandResult = {
    execute: Function,
    isAvailable: Function,
    isApplied: Function,
};

export interface CommandInterface {
    readonly name: string;

    readonly label: string;

    readonly requirementType: CommandRequirementType;

    readonly requirement: Function | null;

    getCommand(type?: MarkType | NodeType, options?: Record<any, any>): GetCommandResult;
}
