import { usePage } from '@inertiajs/react';

import { cn } from '@/lib/utils';

export default function AppWordmark({ className }: { className?: string }) {
    const { name } = usePage().props;

    return (
        <span className={cn('font-medium tracking-tight', className)}>
            <span className="font-extrabold">{name.charAt(0)}</span>
            {name.slice(1)}
        </span>
    );
}
