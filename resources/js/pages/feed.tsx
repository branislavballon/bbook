import { Head } from '@inertiajs/react';
import { Newspaper } from 'lucide-react';
import { EmptyState } from '@/components/empty-state';
import { feed } from '@/routes';

export default function Feed() {
    return (
        <>
            <Head title="Feed" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <EmptyState
                    icon={Newspaper}
                    title="Your feed is empty"
                    description="Posts you write and posts from your friends show up here. Connect with people to start filling it."
                />
            </div>
        </>
    );
}

Feed.layout = {
    breadcrumbs: [
        {
            title: 'Feed',
            href: feed(),
        },
    ],
};
