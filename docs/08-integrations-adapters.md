# Integration Adapters (Phase I Stubs)

| Adapter | Interface | Phase I behavior |
|---------|-----------|------------------|
| CitizensPortalAdapter | linkCitizen, syncProfile | Stub success + logged payload |
| CtoPaymentAdapter | postAssessment, paymentStatus | Stub queued |
| BfpFeeAdapter | feeLines | Stub schedule |
| DpwhFeeAdapter | feeLines | Stub schedule |
| S3Storage | store/retrieve | Local disk until S3 configured |

Enable/disable and endpoints stored in Admin integration settings (encrypted secrets).
