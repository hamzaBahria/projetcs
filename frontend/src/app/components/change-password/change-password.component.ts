import { Component } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { PasswordService } from '../../services/password.service';
import { passwordComplexity } from '../../validators/password.validator';
import { NgIf } from '@angular/common';

@Component({
  selector: 'app-change-password',
  templateUrl: './change-password.component.html',
  styleUrls: ['./change-password.component.scss'],
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, NgIf]
})
export class ChangePasswordComponent {
  passwordForm: FormGroup;
  loading = false;
  error = '';
  success = '';

  constructor(
    private fb: FormBuilder,
    private passwordService: PasswordService
  ) {
    this.passwordForm = this.fb.group({
      current_password: ['', [Validators.required]],
      new_password: ['', [Validators.required, Validators.minLength(8), passwordComplexity()]],
      new_password_confirmation: ['', [Validators.required]]
    }, { validators: this.passwordsMatch });
  }

  passwordsMatch(group: FormGroup) {
    const pass = group.get('new_password')?.value;
    const confirm = group.get('new_password_confirmation')?.value;
    return pass === confirm ? null : { notMatching: true };
  }

  onSubmit(): void {
    if (this.passwordForm.invalid) return;
    this.loading = true;
    this.error = '';
    this.success = '';

    this.passwordService.changePassword({
      current_password: this.passwordForm.value.current_password!,
      new_password: this.passwordForm.value.new_password!,
      new_password_confirmation: this.passwordForm.value.new_password_confirmation!
    }).subscribe({
      next: (res) => {
        if (res.success) {
          this.success = 'Mot de passe modifié avec succès !';
          this.passwordForm.reset();
        } else {
          this.error = res.message || 'Erreur';
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || 'Erreur';
        this.loading = false;
      }
    });
  }
}
