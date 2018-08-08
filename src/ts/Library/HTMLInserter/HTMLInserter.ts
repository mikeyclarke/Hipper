import { InsertPoint } from "./InsertPoint";
export default class HTMLInserter
{
    public static insertHTMLString(
        relativeElement: Element, 
        position: InsertPoint,
        html: string,
    ): void {
        relativeElement.insertAdjacentHTML(position, html);
    }
}
