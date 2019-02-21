export class NameTeamFormData {
    private readonly teamName: string;

    constructor(teamName: string) {
        this.teamName = teamName;
    }

    public get(): string {
        return JSON.stringify({
            name: this.teamName,
        });
    }
}
