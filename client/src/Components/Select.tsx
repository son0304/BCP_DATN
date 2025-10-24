import React, { forwardRef } from 'react';

type OptionType = {
  value: string | number;
  label: string;
};


type SelectProps = React.SelectHTMLAttributes<HTMLSelectElement> & {
  label: string;
  options: OptionType[];
};

const Select = forwardRef<HTMLSelectElement, SelectProps>(({ label, id, options, ...props }, ref) => {
  return (
    <div className="w-full">
      <label htmlFor={id} className="block text-sm font-medium text-gray-700 mb-1">
        {label}
      </label>
      <div className="relative">
        <select
          id={id}
          ref={ref}
          {...props}
          className="appearance-none block w-full px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-[#348738] focus:border-[#348738] sm:text-sm"
        >
          <option value="" disabled>-- Vui lòng chọn --</option>
          
          {options.map((option) => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
        <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
          <svg className="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
          </svg>
        </div>
      </div>
    </div>
  );
});

Select.displayName = 'Select';

export default Select;
