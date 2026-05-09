import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { Router, RouterLink } from '@angular/router';
import { UserService } from '../../services/user.service';
import { AuthService } from '../../services/auth.service';
import { User } from '../../models/user.model';
import { NgIf } from '@angular/common';
import { FormsModule } from '@angular/forms';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.scss'],
  standalone: true,
  imports: [ReactiveFormsModule, RouterLink, NgIf, FormsModule]
})
export class ProfileComponent implements OnInit {
  profileForm: FormGroup;
  user: User | null = null;
  editing = false;
  loading = false;
  error = '';
  success = '';
  avatarPreview: string | null = null;

  deleting = false;
  showDeleteConfirm = false;
  deletePassword = '';

  constructor(
    private fb: FormBuilder,
    private userService: UserService,
    private authService: AuthService,
    private router: Router
  ) {
    this.profileForm = this.fb.group({
      name: ['', [Validators.required]],
      email: ['', [Validators.required, Validators.email]]
    });
  }

  ngOnInit(): void {
    this.loadProfile();
  }

  loadProfile(): void {
    this.userService.getProfile().subscribe({
      next: (res) => {
        if (res.data) {
          this.user = res.data;
          this.profileForm.patchValue({ name: res.data.name, email: res.data.email });
          this.authService.setUser(res.data);
        }
      }
    });
  }

  toggleEdit(): void {
    this.editing = !this.editing;
    if (!this.editing) {
      this.profileForm.patchValue({ name: this.user?.name, email: this.user?.email });
    }
  }

  onSubmit(): void {
    if (this.profileForm.invalid) return;
    this.loading = true;
    this.error = '';
    this.success = '';

    this.userService.updateProfile(this.profileForm.value).subscribe({
      next: (res) => {
        if (res.success) {
          this.success = 'Profil mis à jour';
          this.loadProfile();
          this.editing = false;
        }
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || 'Erreur de mise à jour';
        this.loading = false;
      }
    });
  }

  confirmDelete(): void {
    this.showDeleteConfirm = true;
  }

  cancelDelete(): void {
    this.showDeleteConfirm = false;
    this.deletePassword = '';
  }

  deleteAccount(): void {
    this.deleting = true;
    const data: any = {};
    if (this.deletePassword) data.password = this.deletePassword;
    this.userService.deleteAccount(data).subscribe({
      next: () => {
        this.authService.clearAuth();
        this.router.navigate(['/login']);
      },
      error: (err) => {
        this.error = err.error?.message || 'Échec de la suppression';
        this.deleting = false;
      }
    });
  }

  onFileSelected(event: Event): void {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => this.avatarPreview = reader.result as string;
    reader.readAsDataURL(file);

    const formData = new FormData();
    formData.append('avatar', file);
    this.userService.uploadAvatar(formData).subscribe({
      next: (res) => {
        if (res.success) {
          this.success = 'Avatar mis à jour';
          this.loadProfile();
        }
      },
      error: (err) => {
        this.error = err.error?.message || 'Erreur upload';
      }
    });
  }
}
