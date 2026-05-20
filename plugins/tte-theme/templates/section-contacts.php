<?php if($contact_info = get_field('contact_info', 'options') ): ?>
<div class="container-fluid">
    <div class="section-contacts">
        <?php if($contact_info['contact_person'] || $contact_info['address'] || $contact_info['phone_number'] || $contact_info['fax_number'] || $contact_info['email_address']): ?>
            <h3 class="text-primary">Contacts</h3>
        <?php endif; ?>
        <p>
            <?php if($contact_info['contact_person']): ?>
            <?php echo $contact_info['contact_person']; ?>
            <br>
            <?php endif; ?>
            <?php if($contact_info['address']): ?>
                <?php echo $contact_info['address']; ?>
            <br>
            <?php endif; ?>
            <?php if($contact_info['phone_number']): ?>
                <a class="text-dark pr-3" href="tel:<?php echo $contact_info['phone_number']; ?>"><i class="fa fa-phone text-primary" aria-hidden="true"></i> <?php echo $contact_info['phone_number']; ?></a>
            <?php endif; ?>
            <?php if($contact_info['fax_number']): ?>
                <a class="text-dark pr-3" href="tel:<?php echo $contact_info['fax_number']; ?>"><i class="fa fa-fax text-primary" aria-hidden="true"></i> <?php echo $contact_info['fax_number']; ?></a>
            <?php endif; ?>
            <?php if($contact_info['email_address']): ?>
                 <a class="text-dark pr-3" href="mailto:<?php echo $contact_info['email_address']; ?>"><i class="fa fa-envelope text-primary" aria-hidden="true"></i> <?php echo $contact_info['email_address']; ?></a>
            <?php endif; ?>
        </p>
    </div>
</div>
<?php endif; ?>
