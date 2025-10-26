import React, { forwardRef, useState } from 'react';

type InputProps = React.InputHTMLAttributes<HTMLInputElement> & {
  label: string;
  error?: string;
};

const Input = forwardRef<HTMLInputElement, InputProps>(
  ({ label, id, type = 'text', className = '', error, ...props }, ref) => {
    const [showPassword, setShowPassword] = useState(false);

    const isPassword = type === 'password';
    const actualType = isPassword ? (showPassword ? 'text' : 'password') : type;
    const toggleShow = () => setShowPassword((s) => !s);

    return (
      <div className="w-full">
        {/* Label */}
        <label
          htmlFor={id}
          className="block text-sm font-medium text-gray-700 mb-1"
        >
          {label}
        </label>

        {/* Input + nút toggle */}
        <div className="relative">
          <input
            id={id}
            ref={ref}
            {...props}
            type={actualType}
            className={`block w-full px-3 py-2 pr-${
              isPassword ? '10' : '3'
            } border rounded-md shadow-sm placeholder-gray-400 focus:outline-none sm:text-sm
              ${
                error
                  ? 'border-red-500 focus:ring-red-500 focus:border-red-500'
                  : 'border-gray-300 focus:ring-[#348738] focus:border-[#348738]'
              }
              ${className}
            `}
          />

          {/* Nút ẩn/hiện mật khẩu */}
          {isPassword && (
            <button
              type="button"
              onClick={toggleShow}
              aria-label={showPassword ? 'Hide password' : 'Show password'}
              className="absolute inset-y-0 right-2 flex items-center px-2 text-gray-500 hover:text-gray-700 focus:outline-none"
            >
              {showPassword ? (
                <i className="fa-solid fa-eye-slash"></i>
              ) : (
                <i className="fa-solid fa-eye"></i>
              )}
            </button>
          )}
        </div>

        {/* Thông báo lỗi */}
        {error && (
          <p className="mt-1 text-sm text-red-500">
            {error}
          </p>
        )}
      </div>
    );
  }
);

Input.displayName = 'Input';

export default Input;
