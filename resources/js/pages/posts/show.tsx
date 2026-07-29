import { Head } from '@inertiajs/react';
import { PostCard } from '@/components/post-card';
import { feed } from '@/routes';
import { show } from '@/routes/posts';
import type { Post } from '@/types/posts';

type Props = {
    post: Post;
};

export default function ShowPost({ post }: Props) {
    return (
        <>
            <Head title={`Post by ${post.author.name}`} />

            <div className="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-4 p-4">
                <h1 className="sr-only">Post by {post.author.name}</h1>

                <PostCard post={post} linked={false} />
            </div>
        </>
    );
}

/**
 * A callback rather than a static object, because the trail needs the post's
 * id. Dynamic props via setLayoutProps would outlive this page and follow the
 * person back to the feed.
 */
ShowPost.layout = ({ post }: Props) => ({
    breadcrumbs: [
        { title: 'Feed', href: feed() },
        { title: 'Post', href: show(post.id) },
    ],
});
