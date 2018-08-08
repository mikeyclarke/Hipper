export default interface IControl 
{
    setElement(): void,
    render(data: any): void,
    bindEvents(): void
}
