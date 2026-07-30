import { Form } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import PostController from '@/actions/App/Http/Controllers/PostController';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';

type Props = {
    postId: number;
};

/**
 * Deleting a post is irreversible, so it is confirmed in a dialog first —
 * the same pattern the account deletion screen uses.
 */
export function DeletePostDialog({ postId }: Props) {
    return (
        <Dialog>
            {/*
             * Icon-only, matching the Edit action beside it: named by a
             * tooltip for a pointer, and by a hidden label for everyone else.
             */}
            <Tooltip>
                <TooltipTrigger asChild>
                    <DialogTrigger asChild>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-8"
                            data-test="delete-post-button"
                        >
                            <Trash2 className="size-4" aria-hidden="true" />
                            <span className="sr-only">Delete post</span>
                        </Button>
                    </DialogTrigger>
                </TooltipTrigger>
                <TooltipContent>Delete post</TooltipContent>
            </Tooltip>

            <DialogContent>
                <DialogTitle>Delete this post?</DialogTitle>
                <DialogDescription>
                    Once the post is deleted, it and everything attached to it
                    are gone permanently. This cannot be undone.
                </DialogDescription>

                <Form {...PostController.destroy.form(postId)}>
                    {({ processing }) => (
                        <DialogFooter className="gap-2">
                            <DialogClose asChild>
                                <Button variant="secondary">Cancel</Button>
                            </DialogClose>

                            <Button
                                variant="destructive"
                                disabled={processing}
                                asChild
                            >
                                <button
                                    type="submit"
                                    data-test="confirm-delete-post-button"
                                >
                                    Delete post
                                </button>
                            </Button>
                        </DialogFooter>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}
