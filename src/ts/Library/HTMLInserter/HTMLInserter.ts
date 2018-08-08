import { InsertPoint } from "./InsertPoint";
export default class HTMLInserter
{
    public static insert(
        relativeElement: Element, 
        position: InsertPoint,
        html: any,
    ): void {
        relativeElement.insertAdjacentHTML(position, html);
    }
}
