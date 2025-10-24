import React, { forwardRef } from 'react';
type TextareaProps = React.TextareaHTMLAttributes<HTMLTextAreaElement> & {
    label: string;
};

const Textarea = forwardRef<HTMLTextAreaElement, TextareaProps>(({ label, id, ...props }, ref) => {
    return (
        <div className="w-full">
            <label htmlFor={id} className="block text-sm font-medium text-gray-700 mb-1">
                {label}
            </label>
            <textarea
                id={id}
                ref={ref}
                {...props}
                className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#348738] focus:border-[#348738] sm:text-sm"
            />
        </div>
    );
});

Textarea.displayName = 'Textarea';

export default Textarea;