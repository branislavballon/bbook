import { Newspaper, UserCircle, Users } from 'lucide-react';
import { feed } from '@/routes';
import { index as friends } from '@/routes/friends';
import { show as profile } from '@/routes/users';
import type { NavItem } from '@/types';

/**
 * The primary destinations, shared by the sidebar and header shells so a new
 * destination is added in one place rather than two.
 *
 * A function rather than a constant, because `My Profile` is `users.show`
 * pointed at the viewer — there is no separate route for one's own profile.
 */
export function mainNavItems(viewerId: number): NavItem[] {
    return [
        {
            title: 'Feed',
            href: feed(),
            icon: Newspaper,
        },
        {
            title: 'Friends',
            href: friends(),
            icon: Users,
        },
        {
            title: 'My Profile',
            href: profile(viewerId),
            icon: UserCircle,
        },
    ];
}
