import { Form } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { Send } from 'lucide-react';
import type { ComponentProps } from 'react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

type PostFormData = {
    body: string;
};

type Props = Omit<ComponentProps<typeof Form<PostFormData>>, 'children'> & {
    /**
     * The cap the server enforces, handed down from the page so the figure
     * shown here and the rule that refuses the text are one number.
     */
    maxLength: number;
    defaultValue?: string;
    label?: string;
    placeholder?: string;
    submitLabel?: string;
    /** The glyph beside the submit label, so each composer's verb has a face. */
    submitIcon?: LucideIcon;
};


export function PostForm({
    maxLength,
    defaultValue = '',
    label = 'Write a post',
    placeholder = "What's on your mind?",
    submitLabel = 'Post',
    submitIcon: SubmitIcon = Send,
    ...formProps
}: Props) {
    const [body, setBody] = useState(defaultValue);

    const remaining = maxLength - body.length;

    return (
        <Form<PostFormData>
            {...formProps}
            resetOnSuccess
            onSuccess={(...args) => {
                setBody(defaultValue);
                formProps.onSuccess?.(...args);
            }}
            className="space-y-3"
        >
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
                            value={body}
                            /*
                             * The cap is applied to the value as well as to
                             * the element, so text arriving by paste is cut to
                             * length rather than trusted to `maxLength`.
                             */
                            onChange={(event) =>
                                setBody(event.target.value.slice(0, maxLength))
                            }
                            maxLength={maxLength}
                            placeholder={placeholder}
                            aria-invalid={errors.body ? true : undefined}
                            aria-describedby={
                                errors.body
                                    ? 'body-remaining body-error'
                                    : 'body-remaining'
                            }
                        />

                        <InputError id="body-error" message={errors.body} />
                    </div>

                    <div className="flex items-center justify-between gap-3">
                        {/*
                         * A description rather than a live region: read when
                         * the textarea takes focus, not announced over every
                         * keystroke.
                         */}
                        <p
                            id="body-remaining"
                            aria-live="off"
                            className="text-xs text-muted-foreground"
                            data-test="body-remaining"
                        >
                            {remaining === 1
                                ? '1 character remaining'
                                : `${remaining} characters remaining`}
                        </p>

                        <Button
                            disabled={processing || body.trim() === ''}
                            data-test="submit-post"
                        >
                            <SubmitIcon className="size-4" aria-hidden="true" />
                            {submitLabel}
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
