import React, { forwardRef } from 'react';
type InputProps = React.InputHTMLAttributes<HTMLInputElement> & {
  label: string;
};

const Input = forwardRef<HTMLInputElement, InputProps>(({ label, id, ...props }, ref) => {
  return (
    <div className="w-full">
      <label htmlFor={id} className="block text-sm font-medium text-gray-700 mb-1">
        {label}
      </label>
      <input
        id={id}
        ref={ref}
        {...props}
        className="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-[#348738] focus:border-[#348738] sm:text-sm"
      />
    </div>
  );
});

Input.displayName = 'Input';

export default Input;
