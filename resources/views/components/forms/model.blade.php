<!-- Add or Edit Event Modal -->
<div class="modal fade" id="add-edit-event-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="add-edit-event-modal-title">Add Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="add-event-form" id="add-event-form">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input class="form-control" id="event-location" placeholder="Name" value="Ahmebadad" />
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <textarea class="form-control" id="event-Phone" placeholder="Enter event Phone" rows="3" spellcheck="false">testing....Phone</textarea>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary" id="add-new-event-btn">Add Event ?? Update Event</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <input type="hidden" id="event-id" name="eventid" value="" />
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>