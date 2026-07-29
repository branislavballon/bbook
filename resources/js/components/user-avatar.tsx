import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';

type Props = {
    name: string;
    className?: string;
};

/**
 * A person's avatar, generated from their initials. There are no uploaded
 * images anywhere in this application.
 */
export function UserAvatar({ name, className }: Props) {
    const getInitials = useInitials();

    return (
        <Avatar className={cn('size-10', className)}>
            <AvatarFallback className="bg-neutral-200 text-sm font-medium text-black dark:bg-neutral-700 dark:text-white">
                {getInitials(name)}
            </AvatarFallback>
        </Avatar>
    );
}
