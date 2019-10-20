import ContextMenu from 'components/ContextMenu';
import ContextMenuToggle from 'components/ContextMenuToggle';
import PasswordInput from 'components/PasswordInput';
import EditableFormField from 'components/EditableFormField';
import PopoverAlert from 'components/PopoverAlert';
import FloatingButton from 'components/FloatingButton';
import ElasticTextInput from 'components/ElasticTextInput';
import MobileNavigation from 'components/MobileNavigation';
import MobileNavigationToggle from 'components/MobileNavigationToggle';

export default function loadComponents(): void {
    const components = {
        'context-menu': ContextMenu,
        'context-menu-toggle': ContextMenuToggle,
        'password-input': PasswordInput,
        'editable-form-field': EditableFormField,
        'popover-alert': PopoverAlert,
        'floating-button': FloatingButton,
        'elastic-text-input': ElasticTextInput,
        'mobile-navigation': MobileNavigation,
        'mobile-navigation-toggle': MobileNavigationToggle,
    };

    Object.entries(components).forEach(([name, classDef]) => {
        window.customElements.define(name, classDef);
    });
}
