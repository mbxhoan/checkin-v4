;

import { renderSendMailTable } from "../emails/_renderSendMailTable";
import { handleChangeStatus } from "../emails/_handleChangeStatus";
import { renderEventSelect2 } from "../clients/_renderClientSelect2";

const PREVIEW_FALLBACK_URL = "/admin/email_templates/get-postmark-templates";
const SCHEDULE_PAST_MESSAGE = {
  vi: "Không thể chọn thời gian trong quá khứ",
  en: "Scheduled time cannot be in the past",
};

const getSchedulePastMessage = () => {
  const currentLang = String(document.documentElement.getAttribute("lang") || "").toLowerCase();
  return currentLang.startsWith("vi") ? SCHEDULE_PAST_MESSAGE.vi : SCHEDULE_PAST_MESSAGE.en;
};

const toDateTimeLocalValue = (date = new Date()) => {
  const localDate = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
  return localDate.toISOString().slice(0, 16);
};

const isScheduledAtValid = (scheduledAt = "") => {
  if (!scheduledAt) {
    return true;
  }

  return scheduledAt >= toDateTimeLocalValue();
};

const applyScheduledAtConstraints = () => {
  const $scheduledAt = $("#scheduled_at");
  if (!$scheduledAt.length) {
    return;
  }

  const syncMin = () => {
    $scheduledAt.attr("min", toDateTimeLocalValue());
  };

  syncMin();
  $scheduledAt.on("focus", syncMin);

  $scheduledAt.on("change", function () {
    const value = String($(this).val() || "");
    if (!value || isScheduledAtValid(value)) {
      return;
    }

    notify(getSchedulePastMessage(), "error");
    $(this).focus();
  });
};

const escapeHtml = (value = "") => {
  return String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
};

const notify = (message, icon = "success") => {
  if (window.Swal) {
    Swal.fire({
      toast: true,
      position: "top-end",
      icon,
      title: message,
      showConfirmButton: false,
      timer: 2200,
      timerProgressBar: true,
    });
    return;
  }

  if (icon === "error") {
    console.error(message);
  } else {
    console.log(message);
  }
};

const getCsrfToken = () => {
  const tokenElement = document.querySelector('meta[name="csrf-token"]');
  return tokenElement ? tokenElement.getAttribute("content") : "";
};

const setTemplateCardChecked = ($checkbox) => {
  $(".template-checkbox").prop("checked", false);
  $(".template-card").removeClass("border-primary");

  if ($checkbox && $checkbox.length) {
    $checkbox.prop("checked", true);
    $checkbox.closest("label").find(".template-card").addClass("border-primary");
  }
};

const loadTemplatePreview = (templateId, previewUrl = PREVIEW_FALLBACK_URL, targetPreview = "#campaign-template-preview") => {
  if (!templateId || !$(targetPreview).length) {
    return;
  }

  $(targetPreview).html("Loading...");
  $.ajax({
    url: `${previewUrl}/${templateId}`,
    method: "GET",
    success: function (html) {
      $(targetPreview).html(html);
    },
    error: function () {
      $(targetPreview).html('<div class="text-danger">Error loading template details</div>');
    },
  });
};

const updateTemplateSelect = (templates, selectedId = "", selectSelector = "#template_id") => {
  const $select = $(selectSelector);
  if (!$select.length) {
    return;
  }

  const current = String($select.val() || selectedId || "");
  $select.empty();

  templates.forEach((template) => {
    $select.append(
      $("<option>", {
        value: String(template.id),
        text: template.name,
      }),
    );
  });

  const hasCurrent = templates.some((template) => String(template.id) === current);
  const fallback = templates[0] ? String(templates[0].id) : "";
  const nextValue = hasCurrent ? current : fallback;
  $select.val(nextValue);

  if ($select.hasClass("select2-hidden-accessible")) {
    $select.trigger("change.select2");
  }
  $select.trigger("change");
};

const updateSenderSelect = (senders, selectedEmail = "", selectSelector = "#from_email") => {
  const $select = $(selectSelector);
  if (!$select.length) {
    return;
  }

  const current = String($select.val() || selectedEmail || "");
  $select.empty();

  senders.forEach((sender) => {
    const email = String(sender.email || "");
    if (!email) {
      return;
    }

    $select.append(
      $("<option>", {
        value: email,
        text: sender.label || email,
      }),
    );
  });

  const hasCurrent = senders.some((sender) => String(sender.email || "") === current);
  const fallback = senders[0] ? String(senders[0].email || "") : "";
  const nextValue = hasCurrent ? current : fallback;
  $select.val(nextValue);

  if ($select.hasClass("select2-hidden-accessible")) {
    $select.trigger("change.select2");
  }
  $select.trigger("change");
};

const renderTemplateCards = (templates, selectedId = "", targetGrid = "#campaign-template-grid") => {
  const $grid = $(targetGrid);
  if (!$grid.length) {
    return;
  }

  const current = String(selectedId || $grid.find(".template-checkbox:checked").val() || "");

  const cards = templates.map((template) => {
    const id = escapeHtml(String(template.id));
    const name = escapeHtml(template.name || "");
    const checked = String(template.id) === current ? "checked" : "";

    return `
      <div id="check-item-${id}" class="col-md-4 text-center form-check pb-2">
        <label class="form-control-label w-100 border border-secondary rounded-3 overflow-hidden">
          <div class="card rounded-3 template-card shadow-none border-0" data-id="${id}" style="width:100%; height: 100px;">
            <div class="d-flex h-100 align-items-center justify-content-center fw-semibold">
              ${name}
            </div>
          </div>
          <input type="checkbox" name="template_id" id="template_${id}" class="template-checkbox" value="${id}" ${checked} />
        </label>
      </div>
    `;
  });

  $grid.html(cards.join(""));

  const $checked = $grid.find(".template-checkbox:checked").first();
  setTemplateCardChecked($checked.length ? $checked : null);
};

const syncCampaignTemplates = ($button) => {
  const url = $button.data("url");
  if (!url) {
    return;
  }

  const selectSelector = $button.data("target-select") || "#template_id";
  const targetGrid = $button.data("target-grid") || "#campaign-template-grid";
  const previewUrl =
    $button.data("preview-url") ||
    $(targetGrid).data("preview-url") ||
    PREVIEW_FALLBACK_URL;
  const targetPreview = $button.data("target-preview") || "#campaign-template-preview";
  const selectedId = String($(selectSelector).val() || $(targetGrid).find(".template-checkbox:checked").val() || "");

  $button.prop("disabled", true);

  $.ajax({
    url,
    method: "POST",
    data: {
      _token: getCsrfToken(),
      template_id: selectedId,
    },
    success: function (response) {
      const templates = response && response.data && Array.isArray(response.data.templates)
        ? response.data.templates
        : [];
      if (!Array.isArray(templates) || !templates.length) {
        // notify("No template found after sync", "error");
        toastr.error("No template found after sync");
        return;
      }

      updateTemplateSelect(templates, selectedId, selectSelector);
      renderTemplateCards(templates, selectedId, targetGrid);

      const nextTemplateId = String($(selectSelector).val() || $(targetGrid).find(".template-checkbox:checked").val() || "");
      loadTemplatePreview(nextTemplateId, previewUrl, targetPreview);
      notify((response && response.message) ? response.message : "Synced templates");
    },
    error: function (xhr) {
      const message = xhr && xhr.responseJSON && xhr.responseJSON.message
        ? xhr.responseJSON.message
        : "Sync templates failed";
      notify(message, "error");
    },
    complete: function () {
      $button.prop("disabled", false);
    },
  });
};

const syncCampaignSenders = ($button) => {
  const url = $button.data("url");
  if (!url) {
    return;
  }

  const selectSelector = $button.data("target-select") || "#from_email";
  const selectedEmail = String($(selectSelector).val() || "");
  $button.prop("disabled", true);

  $.ajax({
    url,
    method: "POST",
    data: {
      _token: getCsrfToken(),
      from_email: selectedEmail,
    },
    success: function (response) {
      const senders = response && response.data && Array.isArray(response.data.senders)
        ? response.data.senders
        : [];

      if (!Array.isArray(senders) || !senders.length) {
        // notify("No sender email found after sync", "error");
        toastr.error("No sender email found after sync");
        return;
      }

      updateSenderSelect(senders, selectedEmail, selectSelector);
      notify((response && response.message) ? response.message : "Synced sender emails");
    },
    error: function (xhr) {
      const message = xhr && xhr.responseJSON && xhr.responseJSON.message
        ? xhr.responseJSON.message
        : "Sync sender emails failed";
      notify(message, "error");
    },
    complete: function () {
      $button.prop("disabled", false);
    },
  });
};

$(document).ready(function () {
  handleChangeStatus();
  renderSendMailTable("#table-send-mail");
  applyScheduledAtConstraints();

  if ($("#from_email").length) {
    $("#from_email").select2();
  }

  if ($("#template_id").length) {
    $("#template_id").select2();
  }

  $("#event_id").on("change", function () {
    const eventId = $(this).val();
    if (!eventId) return;
    renderEventSelect2(eventId);
  });

  // Preserve schedule value when user clicks "Gửi mail" confirm modal.
  $(document).on("submit", 'form[action*="/campaign_details/send-mail/"]', function () {
    const $scheduledAt = $("#scheduled_at");
    const scheduledAt = String($scheduledAt.val() || "");
    if (!isScheduledAtValid(scheduledAt)) {
      notify(getSchedulePastMessage(), "error");
      $scheduledAt.focus();
      return false;
    }

    $(this).find('input[name="scheduled_at"]').remove();

    $(this).append(
      $("<input>", {
        type: "hidden",
        name: "scheduled_at",
        value: scheduledAt,
      }),
    );
  });

  $(document).on("submit", 'form[action*="/campaigns/"]', function () {
    const $scheduledAt = $("#scheduled_at");
    if (!$scheduledAt.length) {
      return true;
    }

    const scheduledAt = String($scheduledAt.val() || "");
    if (!isScheduledAtValid(scheduledAt)) {
      notify(getSchedulePastMessage(), "error");
      $scheduledAt.focus();
      return false;
    }

    return true;
  });

  $(document).on("click", ".btn-sync-campaign-templates", function (e) {
    e.preventDefault();
    syncCampaignTemplates($(this));
  });

  $(document).on("click", ".btn-sync-campaign-senders", function (e) {
    e.preventDefault();
    syncCampaignSenders($(this));
  });

  $(document).on("change", ".template-checkbox", function () {
    const $checked = $(this);
    if (!$checked.is(":checked")) {
      return;
    }

    setTemplateCardChecked($checked);
  });

  $(document).on("click", ".template-card", function () {
    const templateId = $(this).data("id");
    const targetGrid = "#campaign-template-grid";
    const previewUrl = $(targetGrid).data("preview-url") || PREVIEW_FALLBACK_URL;

    setTemplateCardChecked($(`#template_${templateId}`));

    $("#templateModal").modal("show");
    $("#templateModalBody").html("Loading...");

    $.ajax({
      url: `${previewUrl}/${templateId}`,
      method: "GET",
      success: function (data) {
        $("#templateModalBody").html(data);
      },
      error: function () {
        $("#templateModalBody").html('<div class="text-danger">Error loading template details</div>');
      },
    });
  });

  $("#template_id").on("change", function () {
    const templateId = $(this).val();
    const $button = $(".btn-sync-campaign-templates").first();
    const previewUrl = $button.data("preview-url") || PREVIEW_FALLBACK_URL;
    const targetPreview = $button.data("target-preview") || "#campaign-template-preview";
    loadTemplatePreview(templateId, previewUrl, targetPreview);
  });
});
