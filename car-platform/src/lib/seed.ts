import { prisma } from './prisma'
import { hashPassword } from './auth'

async function main() {
  console.log('🌱 Début du seeding...')

  // Supprimer les données existantes
  await prisma.message.deleteMany()
  await prisma.car.deleteMany()
  await prisma.agency.deleteMany()
  await prisma.user.deleteMany()

  // Créer des utilisateurs et agences de test
  const hashedPassword = await hashPassword('password123')

  // Agence 1
  const agency1User = await prisma.user.create({
    data: {
      email: 'contact@autoplus.fr',
      password: hashedPassword,
      name: 'Jean Dupont',
      role: 'AGENCY',
      phone: '01 23 45 67 89'
    }
  })

  const agency1 = await prisma.agency.create({
    data: {
      name: 'Auto Plus',
      description: 'Spécialiste de la vente et location de véhicules d\'occasion de qualité depuis 1995.',
      address: '123 Avenue des Champs-Élysées, 75008 Paris',
      phone: '01 23 45 67 89',
      email: 'contact@autoplus.fr',
      website: 'https://www.autoplus.fr',
      ownerId: agency1User.id
    }
  })

  // Agence 2
  const agency2User = await prisma.user.create({
    data: {
      email: 'info@carauto.fr',
      password: hashedPassword,
      name: 'Marie Martin',
      role: 'AGENCY',
      phone: '01 98 76 54 32'
    }
  })

  const agency2 = await prisma.agency.create({
    data: {
      name: 'Car Auto',
      description: 'Votre partenaire de confiance pour l\'achat et la location de véhicules.',
      address: '456 Rue de la République, 69002 Lyon',
      phone: '01 98 76 54 32',
      email: 'info@carauto.fr',
      website: 'https://www.carauto.fr',
      ownerId: agency2User.id
    }
  })

  // Client de test
  const client = await prisma.user.create({
    data: {
      email: 'client@test.fr',
      password: hashedPassword,
      name: 'Pierre Client',
      role: 'CLIENT',
      phone: '06 12 34 56 78'
    }
  })

  // Voitures pour l'agence 1
  const cars1 = [
    {
      brand: 'Peugeot',
      model: '308',
      year: 2020,
      color: 'Blanc',
      mileage: 45000,
      fuel: 'DIESEL',
      transmission: 'MANUELLE',
      price: 18500,
      type: 'VENTE',
      description: 'Véhicule en excellent état, entretien régulier, non fumeur.'
    },
    {
      brand: 'Renault',
      model: 'Clio',
      year: 2019,
      color: 'Rouge',
      mileage: 32000,
      fuel: 'ESSENCE',
      transmission: 'AUTOMATIQUE',
      price: 35,
      type: 'LOCATION',
      description: 'Parfaite pour vos déplacements en ville, climatisation, GPS intégré.'
    },
    {
      brand: 'Volkswagen',
      model: 'Golf',
      year: 2021,
      color: 'Gris',
      mileage: 28000,
      fuel: 'HYBRIDE',
      transmission: 'AUTOMATIQUE',
      price: 22000,
      type: 'VENTE',
      description: 'Véhicule hybride économique, garantie constructeur restante.'
    }
  ]

  // Voitures pour l'agence 2
  const cars2 = [
    {
      brand: 'BMW',
      model: 'Série 3',
      year: 2019,
      color: 'Noir',
      mileage: 55000,
      fuel: 'DIESEL',
      transmission: 'AUTOMATIQUE',
      price: 28000,
      type: 'VENTE',
      description: 'Berline premium, cuir, navigation, excellent état général.'
    },
    {
      brand: 'Tesla',
      model: 'Model 3',
      year: 2022,
      color: 'Blanc',
      mileage: 15000,
      fuel: 'ELECTRIQUE',
      transmission: 'AUTOMATIQUE',
      price: 45000,
      type: 'VENTE',
      description: 'Véhicule électrique dernière génération, autopilot, superchargeur.'
    },
    {
      brand: 'Citroën',
      model: 'C3',
      year: 2020,
      color: 'Bleu',
      mileage: 25000,
      fuel: 'ESSENCE',
      transmission: 'MANUELLE',
      price: 25,
      type: 'LOCATION',
      description: 'Citadine confortable, idéale pour la ville et les courts trajets.'
    }
  ]

  // Créer les voitures
  for (const carData of cars1) {
    await prisma.car.create({
      data: {
        ...carData,
        images: JSON.stringify([]),
        agencyId: agency1.id
      }
    })
  }

  for (const carData of cars2) {
    await prisma.car.create({
      data: {
        ...carData,
        images: JSON.stringify([]),
        agencyId: agency2.id
      }
    })
  }

  console.log('✅ Seeding terminé avec succès!')
  console.log('📧 Comptes de test créés:')
  console.log('  Agence 1: contact@autoplus.fr / password123')
  console.log('  Agence 2: info@carauto.fr / password123')
  console.log('  Client: client@test.fr / password123')
}

main()
  .catch((e) => {
    console.error('❌ Erreur lors du seeding:', e)
    process.exit(1)
  })
  .finally(async () => {
    await prisma.$disconnect()
  })