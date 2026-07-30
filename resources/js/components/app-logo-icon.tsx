import type { SVGAttributes } from 'react';

/**
 * The application's mark: a pair of spectacles resting on an open book.
 *
 * One monochrome path — the lenses are holes punched by `evenodd` rather than
 * strokes — so the mark inherits its colour from wherever it is placed and
 * stays legible scaled down to the sidebar's 20px.
 */
export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M6.5 3a3 3 0 1 0 0 6a3 3 0 1 0 0-6ZM6.5 4.6a1.4 1.4 0 1 0 0 2.8a1.4 1.4 0 1 0 0-2.8ZM17.5 3a3 3 0 1 0 0 6a3 3 0 1 0 0-6ZM17.5 4.6a1.4 1.4 0 1 0 0 2.8a1.4 1.4 0 1 0 0-2.8ZM9.4 5.4H14.6V6.6H9.4ZM2 8.8L11.2 11.4V21.6L2 19ZM22 8.8L12.8 11.4V21.6L22 19Z"
            />
        </svg>
    );
}
