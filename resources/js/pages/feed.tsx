import { Head } from '@inertiajs/react';
import { Newspaper } from 'lucide-react';
import PostController from '@/actions/App/Http/Controllers/PostController';
import { EmptyState } from '@/components/empty-state';
import { PostCard } from '@/components/post-card';
import { PostForm } from '@/components/post-form';
import { Card, CardContent } from '@/components/ui/card';
import { feed } from '@/routes';
import type { Post } from '@/types/posts';

type Props = {
    posts: Post[];
};

export default function Feed({ posts }: Props) {
    return (
        <>
            <Head title="Feed" />

            <div className="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-4 p-4">
                <h1 className="sr-only">Feed</h1>

                <Card>
                    <CardContent>
                        <PostForm {...PostController.store.form()} />
                    </CardContent>
                </Card>

                {posts.length === 0 ? (
                    <EmptyState
                        icon={Newspaper}
                        title="Your feed is empty"
                        description="Posts you write and posts from your friends show up here. Write your first post above."
                    />
                ) : (
                    <div className="flex flex-col gap-4">
                        {posts.map((post) => (
                            <PostCard key={post.id} post={post} />
                        ))}
                    </div>
                )}
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
