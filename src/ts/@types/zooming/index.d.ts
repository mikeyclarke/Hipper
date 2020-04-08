type CustomSize = {
    height: number;

    width: number;
};

interface EventHook {
    (target: Element): void;
}

interface ZoomingOptions {
    bgColor?: string;

    bgOpacity?: number;

    closeOnWindowResize?: boolean;

    customSize?: CustomSize | string;

    enableGrab?: boolean;

    preloadImage?: boolean;

    scaleBase?: number;

    scaleExtra?: number;

    scrollThreshold?: number;

    transitionDuration?: number;

    transitionTimingFunction?: string;

    zIndex?: number;

    onBeforeOpen?: EventHook;

    onOpen?: EventHook;

    onBeforeGrab?: EventHook;

    onGrab?: EventHook;

    onMove?: EventHook;

    onBeforeRelease?: EventHook;

    onRelease?: EventHook;

    onBeforeClose?: EventHook;

    onClose?: EventHook;

    onImageLoading?: EventHook;

    onImageLoaded?: EventHook;
}

declare class Zooming {
    constructor(options: ZoomingOptions);

    listen(el: string): Zooming;

    listen(el: Element): Zooming;

    config(): ZoomingOptions;

    config(options: ZoomingOptions): Zooming;

    open(el: string, callback?: EventHook): Zooming;

    open(el: Element, callback?: EventHook): Zooming;

    close(callback?: EventHook): Zooming;

    grab(x: number, y: number, scaleExtra?: number, callback?: EventHook): Zooming;

    move(x: number, y: number, scaleExtra?: number, callback?: EventHook): Zooming;

    release(callback?: EventHook): Zooming;
}

declare module 'zooming' {
    export default Zooming;
}
