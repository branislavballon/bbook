import { Newspaper } from 'lucide-react';
import { feed } from '@/routes';
import type { NavItem } from '@/types';

/**
 * The primary destinations, shared by the sidebar and header shells so a new
 * destination is added in one place rather than two.
 */
export const mainNavItems: NavItem[] = [
    {
        title: 'Feed',
        href: feed(),
        icon: Newspaper,
    },
];
