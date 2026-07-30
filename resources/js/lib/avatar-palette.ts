/**
 * The colours an avatar can take, as complete literal class strings.
 *
 */
const AVATAR_COLOURS = [
    'bg-fuchsia-100 text-fuchsia-900 dark:bg-fuchsia-900 dark:text-fuchsia-100',
    'bg-sky-100 text-sky-900 dark:bg-sky-900 dark:text-sky-100',
    'bg-lime-100 text-lime-900 dark:bg-lime-900 dark:text-lime-100',
    'bg-red-100 text-red-900 dark:bg-red-900 dark:text-red-100',
    'bg-green-100 text-green-900 dark:bg-green-900 dark:text-green-100',
    'bg-yellow-100 text-yellow-900 dark:bg-yellow-900 dark:text-yellow-100',
    'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100',
    'bg-violet-100 text-violet-900 dark:bg-violet-900 dark:text-violet-100',
    'bg-teal-100 text-teal-900 dark:bg-teal-900 dark:text-teal-100',
    'bg-pink-100 text-pink-900 dark:bg-pink-900 dark:text-pink-100',
] as const;

export function avatarColourClasses(name: string): string {
    let total = 0;

    for (const character of name.trim().toLowerCase()) {
        total += character.codePointAt(0) ?? 0;
    }

    return AVATAR_COLOURS[total % AVATAR_COLOURS.length];
}
