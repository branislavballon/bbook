import { Head } from '@inertiajs/react';
import PostController from '@/actions/App/Http/Controllers/PostController';
import Heading from '@/components/heading';
import { PostForm } from '@/components/post-form';
import { Card, CardContent } from '@/components/ui/card';
import { feed } from '@/routes';
import { edit, show } from '@/routes/posts';

type Props = {
    post: {
        id: number;
        body: string;
    };
};

export default function EditPost({ post }: Props) {
    return (
        <>
            <Head title="Edit post" />

            <div className="mx-auto flex w-full max-w-2xl flex-1 flex-col gap-4 p-4">
                <h1 className="sr-only">Edit post</h1>

                <Heading
                    title="Edit post"
                    description="Change what you wrote and save it."
                />

                <Card>
                    <CardContent>
                        <PostForm
                            {...PostController.update.form(post.id)}
                            defaultValue={post.body}
                            label="Edit your post"
                            submitLabel="Save changes"
                        />
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

/** Derived from the post, so a callback rather than a static object. */
EditPost.layout = ({ post }: Props) => ({
    breadcrumbs: [
        { title: 'Feed', href: feed() },
        { title: 'Post', href: show(post.id) },
        { title: 'Edit', href: edit(post.id) },
    ],
});
