import Zooming from 'zooming';

export default class ViewDocumentController {
    public start(routeParameters: object): void {
        this.setUpImageViewer();
    }

    private setUpImageViewer(): void {
        const z = new Zooming({
            customSize: '100%',
        });
        z.listen('.js-document-content img');
    }
}
