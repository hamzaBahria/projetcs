import { AbstractControl, ValidationErrors, ValidatorFn } from '@angular/forms';

export function passwordComplexity(): ValidatorFn {
  return (control: AbstractControl): ValidationErrors | null => {
    const value = control.value || '';
    const hasUpper = /[A-Z]/.test(value);
    const hasLower = /[a-z]/.test(value);
    const hasDigit = /\d/.test(value);
    const hasSpecial = /[@$!%*?&]/.test(value);
    const valid = hasUpper && hasLower && hasDigit && hasSpecial;
    return valid ? null : { complexity: true };
  };
}
