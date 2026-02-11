<div>
    <form>
        <!-- Nomor HP -->
        <div>
            <label for="no_hp">Nomor HP</label>
            <input type="text" id="no_hp" wire:model="no_hp">
        </div>

        <!-- Nama Donatur -->
        <div>
            <label for="nama_donatur">Nama Donatur</label>
            <input type="text" id="nama_donatur" wire:model="nama_donatur">
        </div>

        <!-- Jenis Kelamin -->
        <div>
            <label for="jenis_kelamin">Jenis Kelamin</label>
            <select id="jenis_kelamin" wire:model="jenis_kelamin">
                <option value="L">Laki-Laki</option>
                <option value="P">Perempuan</option>
            </select>
        </div>

        <!-- Email -->
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" wire:model="email">
        </div>

        <!-- Alamat -->
        <div>
            <label for="alamat">Alamat</label>
            <input type="text" id="alamat" wire:model="alamat">
        </div>

        <!-- Sosmed Account -->
        <div>
            <label for="sosmed_account">Sosmed Account</label>
            <input type="text" id="sosmed_account" wire:model="sosmed_account">
        </div>

        <!-- Program -->
        <div>
            <label for="program">Program</label>
            <input type="text" id="program" wire:model="program">
        </div>

        <!-- Channel -->
        <div>
            <label for="channel">Channel</label>
            <input type="text" id="channel" wire:model="channel">
        </div>

        <!-- Fundraiser -->
        <div>
            <label for="fundraiser">Fundraiser</label>
            <input type="text" id="fundraiser" wire:model="fundraiser">
        </div>
    </form>
</div>
