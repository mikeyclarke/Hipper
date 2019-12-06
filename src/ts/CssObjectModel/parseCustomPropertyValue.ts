const floatRegex = /(.\d+)/;

export default function parseCustomPropertyValue(
    element: HTMLElement,
    customPropertyName: string,
    defaultValue: any = null,
    convertToFloat: boolean = false
): any {
    const value = window.getComputedStyle(element).getPropertyValue(customPropertyName).trim();
    if (value === '') {
        return defaultValue;
    }

    if (!convertToFloat) {
        return value;
    }

    if (!isNaN(Number(value))) {
        return value;
    }

    const matches = value.match(floatRegex);
    if (null === matches || matches.length === 0) {
        return defaultValue;
    }

    const float = Number(matches[0]);
    if (isNaN(float)) {
        return defaultValue;
    }

    return float;
}
