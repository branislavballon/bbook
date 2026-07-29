import type { LucideIcon } from 'lucide-react';

type Props = {
    icon: LucideIcon;
    title: string;
    description: string;
};

export function EmptyState({ icon: Icon, title, description }: Props) {
    return (
        <div className="flex flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 px-6 py-16 text-center dark:border-sidebar-border">
            <Icon
                className="mb-4 size-8 text-muted-foreground"
                aria-hidden="true"
            />
            <h2 className="text-lg font-medium">{title}</h2>
            <p className="mt-1 max-w-md text-sm text-muted-foreground">
                {description}
            </p>
        </div>
    );
}
