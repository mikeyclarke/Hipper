import ContextMenu from 'components/ContextMenu';
import ContextMenuFactory from 'components/Factory/ContextMenuFactory';
import HtmlElementIdGenerator from 'IdGenerator/HtmlElementIdGenerator';
import parseCustomPropertyValue from 'CssObjectModel/parseCustomPropertyValue';

const OVERFLOW_BUTTON_CONTAINER_CLASSNAME = 'c-breadcrumb-list__overflow';
const OVERFLOW_BUTTON_CLASSNAME = 'c-breadcrumb-list__overflow-button';
const OVERFLOW_BUTTON_LABEL = 'Show preceeding breadcrumbs';
const OVERFLOW_BUTTON_ICON_NAME = 'more';
const OVERFLOW_BUTTON_ICON_CLASSNAME = 'c-breadcrumb-list__overflow-button-icon';
const OVERFLOW_BUTTON_DIVIDER_ICON_NAME = 'chevron-right';
const OVERFLOW_BUTTON_DIVIDER_ICON_CLASSNAME = 'c-breadcrumb-list__divider';

export default class BreadcrumbList extends HTMLElement {
    public _overflowContainerWidth: number;
    public _list: HTMLOListElement | null = null;
    public _breadcrumbWidthCache: Map<HTMLLIElement, number> | null = null;
    public _breadcrumbOverflowMap: Map<HTMLLIElement, HTMLAnchorElement> | null = null;
    public _activeBreadcrumb: HTMLLIElement | null = null;
    public _overflowContainer: HTMLDivElement | null = null;
    public _overflowContextMenu: ContextMenu | null = null;
    public _overflowItemsContainer: HTMLDivElement | null = null;
    public _resizeEventListener: any | null = null;
    public _mediaQueryList: MediaQueryList | null = null;
    private _hoverFeatureChangeListener: any | null = null;

    constructor() {
        super();

        this._overflowContainerWidth = parseCustomPropertyValue(this, '--overflow-container-width', 0, true);

        const list = this.querySelector('.js-list');
        if (!(list instanceof HTMLOListElement)) {
            throw new Error('breadcrumb-list list element does not exist or is not an instance of HTMLOListElement.');
        }

        this._list = list;

        const activeBreadcrumb = this._list.querySelector('.js-active-breadcrumb');
        if (!(activeBreadcrumb instanceof HTMLLIElement)) {
            throw new Error('breadcrumb-list active breadcrumb does not exist of is not an instance of HTMLLIElement');
        }

        this._activeBreadcrumb = activeBreadcrumb;
    }

    public connectedCallback(): void {
        if (!this.isConnected || null === this._list || null === this._activeBreadcrumb) {
            return;
        }

        this._mediaQueryList = window.matchMedia('(hover: hover)');
        this._hoverFeatureChangeListener = onHoverMediaFeatureChange.bind(this);
        this._mediaQueryList.addListener(this._hoverFeatureChangeListener);

        if (this._mediaQueryList.matches) {
            return;
        }

        setUpCollapsingBreadcrumbs.bind(this)();
    }

    public disconnectedCallback(): void {
        if (null !== this._mediaQueryList && null !== this._hoverFeatureChangeListener) {
            this._mediaQueryList.removeListener(this._hoverFeatureChangeListener);
        }

        if (null !== this._resizeEventListener) {
            window.removeEventListener('resize', this._resizeEventListener);
        }
    }
}

function onHoverMediaFeatureChange(this: BreadcrumbList, event: MediaQueryListEvent): void {
    if (null === this._mediaQueryList) {
        return;
    }

    if (!this._mediaQueryList.matches) {
        setUpCollapsingBreadcrumbs.bind(this)();
        return;
    }

    clearDownCollapsingBreadcrumbs.bind(this)();
}

function setUpCollapsingBreadcrumbs(this: BreadcrumbList): void {
    this._resizeEventListener = collapseOverflowingBreadcrumbs.bind(this);
    window.addEventListener('resize', this._resizeEventListener);

    createCache.bind(this)();
    createOverflowElementOriginalElementMap.bind(this)();
    createOverflowElements.bind(this)();
    collapseOverflowingBreadcrumbs.bind(this)();
}

function clearDownCollapsingBreadcrumbs(this: BreadcrumbList): void {
    if (null !== this._resizeEventListener) {
        window.removeEventListener('resize', this._resizeEventListener);
    }

    uncollapseAll.bind(this)();
    if (null !== this._overflowContainer) {
        this.removeChild(this._overflowContainer);
    }

    this._breadcrumbWidthCache = null;
    this._breadcrumbOverflowMap = null;
    this._overflowContainer = null;
    this._overflowItemsContainer = null;
    this._overflowContextMenu = null;
}

function createCache(this: BreadcrumbList): void {
    if (null === this._list) {
        return;
    }

    const collapsableBreadcrumbs = Array.from(this._list.querySelectorAll('.js-collapsable-breadcrumb'));
    this._breadcrumbWidthCache = new Map();
    collapsableBreadcrumbs.reverse().forEach((breadcrumb) => {
        if (null === this._breadcrumbWidthCache) {
            // Fuck you, TypeScript.
            return;
        }
        this._breadcrumbWidthCache.set(<HTMLLIElement> breadcrumb, breadcrumb.getBoundingClientRect().width);
    });
}

function createOverflowElementOriginalElementMap(this: BreadcrumbList): void {
    if (null === this._breadcrumbWidthCache) {
        return;
    }

    this._breadcrumbOverflowMap = new Map();
    this._breadcrumbWidthCache.forEach((value: number, key: HTMLLIElement) => {
        if (null === this._breadcrumbOverflowMap) {
            // Fuck you again, TypeScript. Twat.
            return;
        }
        const link = <HTMLAnchorElement> key.querySelector('a');
        const element = document.createElement('a');
        element.className = 'c-context-menu__item';
        element.setAttribute('role', 'menuitem');
        element.href = link.href;
        element.textContent = link.textContent;
        this._breadcrumbOverflowMap.set(key, element);
    });
}

function collapseOverflowingBreadcrumbs(this: BreadcrumbList): void {
    if (null === this._activeBreadcrumb || null === this._breadcrumbWidthCache || null === this._overflowContainer ||
        null === this._overflowContextMenu || null === this._overflowItemsContainer ||
        null === this._breadcrumbOverflowMap || null === this._list
    ) {
        return;
    }

    let listWidth = this._list.getBoundingClientRect().width;
    if (!this._overflowContainer.hidden) {
        listWidth = listWidth + this._overflowContainerWidth;
    }
    const activeBreadcrumbWidth = this._activeBreadcrumb.getBoundingClientRect().width;
    const spaceAvailable = (listWidth - activeBreadcrumbWidth);

    let spaceRemaining = spaceAvailable;
    this._breadcrumbWidthCache.forEach((value, key) => {
        spaceRemaining -= <number> value;
    });

    if (spaceRemaining > 0) {
        uncollapseAll.bind(this)();
        this._overflowContainer.hidden = true;
        if (this._overflowContextMenu.expanded) {
            this._overflowContextMenu.expanded = false;
        }
        return;
    }

    this._overflowContainer.hidden = false;
    while (this._overflowItemsContainer.firstChild) {
        this._overflowItemsContainer.removeChild(this._overflowItemsContainer.firstChild);
    }

    const newSpaceAvailable = (spaceAvailable - this._overflowContainerWidth);
    let newSpaceRemaining = newSpaceAvailable;
    this._breadcrumbWidthCache.forEach((value, key) => {
        if (null === this._overflowItemsContainer || null === this._breadcrumbOverflowMap) {
            // 🤬
            return;
        }
        newSpaceRemaining = newSpaceRemaining - value;
        const hide = (newSpaceRemaining < 0);
        key.hidden = hide;
        if (hide) {
            this._overflowItemsContainer.insertBefore(
                <HTMLAnchorElement> this._breadcrumbOverflowMap.get(key),
                this._overflowItemsContainer.firstChild
            );
        }
    });
}

function uncollapseAll(this: BreadcrumbList): void {
    if (null !== this._breadcrumbWidthCache) {
        this._breadcrumbWidthCache.forEach((value, key) => {
            key.hidden = false;
        });
    }
}

function createOverflowElements(this: BreadcrumbList): void {
    this._overflowContainer = document.createElement('div');
    this._overflowContainer.hidden = true;
    this._overflowContainer.className = OVERFLOW_BUTTON_CONTAINER_CLASSNAME;
    this._overflowContainer.innerHTML = generateSvgIconHtml(
        OVERFLOW_BUTTON_DIVIDER_ICON_NAME,
        OVERFLOW_BUTTON_DIVIDER_ICON_CLASSNAME
    );

    const generator = new HtmlElementIdGenerator();
    const contextMenuId = generator.generate('breadcrumb-overflow-list');

    const contextMenuFactory = new ContextMenuFactory();
    this._overflowContextMenu = contextMenuFactory.create(contextMenuId, 'left');

    this._overflowItemsContainer = document.createElement('div');
    this._overflowItemsContainer.className = 'c-context-menu__group';

    this._overflowContextMenu.children[0].insertBefore(
        this._overflowItemsContainer,
        this._overflowContextMenu.children[0].firstChild
    );

    const contextToggle = document.createElement('context-menu-toggle');

    const button = document.createElement('button');
    button.className = OVERFLOW_BUTTON_CLASSNAME;
    button.classList.add('js-button');
    button.innerHTML = generateSvgIconHtml(
        OVERFLOW_BUTTON_ICON_NAME,
        OVERFLOW_BUTTON_ICON_CLASSNAME
    );
    button.setAttribute('aria-controls', contextMenuId);
    button.setAttribute('aria-haspopup', 'menu');
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('aria-label', OVERFLOW_BUTTON_LABEL);

    contextToggle.appendChild(button);

    this._overflowContainer.insertBefore(this._overflowContextMenu, this._overflowContainer.firstChild);
    this._overflowContainer.insertBefore(contextToggle, this._overflowContainer.firstChild);

    this.insertBefore(this._overflowContainer, this.firstChild);
}

function generateSvgIconHtml(name: string, className: string): string {
    return `<svg class="${className}" aria-hidden="true"><use href="#icon-sprite__${name}"/></svg>`;
}
