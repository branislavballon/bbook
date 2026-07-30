import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { avatarColourClasses } from '@/lib/avatar-palette';
import { cn } from '@/lib/utils';

type Props = {
    name: string;
    className?: string;
};

export function UserAvatar({ name, className }: Props) {
    const getInitials = useInitials();

    return (
        <Avatar className={cn('size-10', className)}>
            <AvatarFallback
                className={cn('text-sm font-medium', avatarColourClasses(name))}
            >
                {getInitials(name)}
            </AvatarFallback>
        </Avatar>
    );
}
