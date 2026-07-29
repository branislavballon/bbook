import { Form } from '@inertiajs/react';
import type { ComponentProps } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type PostFormData = {
    body: string;
};

type Props = Omit<ComponentProps<typeof Form<PostFormData>>, 'children'> & {
    defaultValue?: string;
    label?: string;
    placeholder?: string;
    submitLabel?: string;
};

/**
 * The shared post composer, used both by the feed and by the edit page. The
 * action and method are supplied by the caller as Wayfinder form props.
 */
export function PostForm({
    defaultValue = '',
    label = 'Write a post',
    placeholder = "What's on your mind?",
    submitLabel = 'Post',
    ...formProps
}: Props) {
    return (
        <Form<PostFormData> {...formProps} resetOnSuccess className="space-y-3">
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="body" className="sr-only">
                            {label}
                        </Label>

                        <Textarea
                            id="body"
                            name="body"
                            rows={3}
                            defaultValue={defaultValue}
                            placeholder={placeholder}
                            aria-invalid={errors.body ? true : undefined}
                            aria-describedby={
                                errors.body ? 'body-error' : undefined
                            }
                        />

                        <InputError id="body-error" message={errors.body} />
                    </div>

                    <div className="flex justify-end">
                        <Button disabled={processing} data-test="submit-post">
                            {submitLabel}
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
