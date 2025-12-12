<div class="screen kyc-screen">
    <!-- Header -->
    <div class="investment-header">
        <x-link :href="route('more')" class="btn-back" icon="arrow-left" />
        <h1>KYC Verification</h1>
    </div>

    <div class="kyc-container">
        <!-- KYC Status Card -->
        <div class="kyc-status-card">
            <div class="status-icon-container status-{{ $kycStatus }}">
                @if ($kycStatus === 'verified')
                    <i class="bi bi-check-circle-fill"></i>
                @elseif($kycStatus === 'rejected')
                    <i class="bi bi-x-circle-fill"></i>
                @else
                    <i class="bi bi-clock-history"></i>
                @endif
            </div>

            <h2 class="status-title">
                @if ($kycStatus === 'verified')
                    KYC Verified
                @elseif($kycStatus === 'rejected')
                    KYC Rejected
                @elseif($kycStatus === 'pending')
                    KYC Pending Review
                @else
                    KYC Not Submitted
                @endif
            </h2>

            <p class="status-description">
                @if ($kycStatus === 'verified')
                    Your identity has been verified successfully. You have full access to all features.
                @elseif($kycStatus === 'rejected')
                    Your KYC submission was rejected. Please resubmit with correct documents.
                @elseif($kycStatus === 'pending')
                    Your KYC documents are under review. This usually takes 1-2 business days.
                @else
                    Complete your KYC verification to unlock all features and higher transaction limits.
                @endif
            </p>

            <div class="status-badge-large status-{{ $kycStatus }}">
                {{ ucfirst($kycStatus) }}
            </div>
        </div>

        <!-- Current KYC Information (if exists) -->
        @if ($documentType && $documentNumber)
            <div class="kyc-info-card">
                <h3 class="section-title">Submitted Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Document Type</span>
                        <span class="info-value">{{ ucfirst(str_replace('_', ' ', $documentType)) }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Document Number</span>
                        <span class="info-value">{{ $documentNumber }}</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Uploaded Documents -->
        @if ($uploadedDocuments->count() > 0)
            <div class="documents-section">
                <h3 class="section-title">Uploaded Documents</h3>
                <div class="documents-grid">
                    @foreach ($uploadedDocuments as $document)
                        <div class="document-card" wire:key="doc-{{ $document->id }}">
                            <div class="document-icon">
                                @if ($document->mime_type === 'application/pdf')
                                    <i class="bi bi-file-pdf"></i>
                                @else
                                    <i class="bi bi-file-image"></i>
                                @endif
                            </div>
                            <div class="document-info">
                                <h4>{{ $document->name }}</h4>
                                <p>{{ $document->human_readable_size }}</p>
                            </div>
                            <div class="document-actions">
                                {{-- <a href="{{ $document->getUrl() }}" target="_blank" class="doc-action-btn view">
                                    <i class="bi bi-eye"></i>
                                </a> --}}
                                @if ($kycStatus !== 'verified')
                                    <button wire:click="deleteDocument({{ $document->id }})"
                                        class="doc-action-btn delete" wire:loading.attr="disabled"
                                        wire:confirm="Are you sure you want to delete this document?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Submit/Resubmit KYC Button -->
        @if ($kycStatus !== 'verified')
            <div class="kyc-actions">
                <button wire:click="openSubmitModal" class="btn-submit-kyc">
                    <i class="bi bi-upload"></i>
                    {{ $kycStatus === 'rejected' || ($documentType && $documentNumber) ? 'Resubmit KYC' : 'Submit KYC' }}
                </button>
            </div>
        @endif

        <!-- KYC Requirements Info -->
        <div class="kyc-requirements-card">
            <h3 class="section-title">
                <i class="bi bi-info-circle"></i>
                Requirements
            </h3>
            <ul class="requirements-list">
                <li>
                    <i class="bi bi-check-circle"></i>
                    <span>Valid government-issued ID (Passport, National ID, Driver's License, or Voter ID)</span>
                </li>
                <li>
                    <i class="bi bi-check-circle"></i>
                    <span>Clear, readable photos/scans of both sides of your document</span>
                </li>
                <li>
                    <i class="bi bi-check-circle"></i>
                    <span>Documents must be in JPG, PNG, or PDF format</span>
                </li>
                <li>
                    <i class="bi bi-check-circle"></i>
                    <span>Maximum file size: 5MB per document</span>
                </li>
                <li>
                    <i class="bi bi-check-circle"></i>
                    <span>Document must not be expired</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Submit KYC Modal -->
    <x-bottom-sheet id="showSubmitModal" title="Submit KYC Documents">
        <div class="submit-kyc-form">
            <!-- Document Type -->
            <div class="form-group">
                <label class="form-label">Document Type *</label>
                <select wire:model="documentType" class="form-select">
                    <option value="">Select document type...</option>
                    <option value="passport">Passport</option>
                    <option value="national_id">National ID Card</option>
                    <option value="driver_license">Driver's License</option>
                    <option value="voter_card">Voter ID Card</option>
                </select>
                @error('documentType')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Document Number -->
            <div class="form-group">
                <label class="form-label">Document Number *</label>
                <input type="text" wire:model="documentNumber" class="form-input"
                    placeholder="Enter your document number">
                @error('documentNumber')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- File Upload -->
            <div class="form-group">
                <label class="form-label">Upload Documents * (Max 5 files)</label>
                <input type="file" wire:model="documents" class="form-input" multiple accept=".jpg,.jpeg,.png,.pdf">
                <small class="form-hint">
                    Upload clear photos/scans of your document (JPG, PNG, or PDF - Max 5MB each)
                </small>
                @error('documents')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
                @error('documents.*')
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Upload Preview -->
            @if ($documents)
                <div class="upload-preview">
                    <h4>Selected Files:</h4>
                    <ul>
                        @foreach ($documents as $document)
                            <li>{{ $document->getClientOriginalName() }} ({{ round($document->getSize() / 1024, 2) }}
                                KB)</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="info-box">
                <i class="bi bi-shield-check"></i>
                <div>
                    <strong>Your privacy matters</strong>
                    <p>All documents are encrypted and stored securely. We will only use them for verification purposes.
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons gap-2">
                <button wire:click="closeSubmitModal" class="btn-cancel">
                    Cancel
                </button>
                <button wire:click="submitKyc" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submitKyc">
                        <i class="bi bi-upload"></i>
                        Submit KYC
                    </span>
                    <span wire:loading wire:target="submitKyc">
                        <x-spinner />
                        Uploading...
                    </span>
                </button>
            </div>
        </div>
    </x-bottom-sheet>

    <livewire:mobile-app.component.bottom-nav />
</div>
