import { PrismaClient } from "@prisma/client";

const prisma = new PrismaClient();

async function main() {
  await prisma.reservation.deleteMany();
  await prisma.lead.deleteMany();
  await prisma.listing.deleteMany();
  await prisma.car.deleteMany();
  await prisma.agency.deleteMany();

  const agency1 = await prisma.agency.create({
    data: {
      name: "City Auto",
      email: "contact@cityauto.example",
      phone: "+33 1 23 45 67 89",
      city: "Paris",
      country: "France",
      description: "Vente et location de voitures à Paris.",
      logoUrl: null,
    },
  });

  const agency2 = await prisma.agency.create({
    data: {
      name: "Riviera Cars",
      email: "hello@rivieracars.example",
      phone: "+33 4 93 00 00 00",
      city: "Nice",
      country: "France",
      description: "Agence de voitures sur la Côte d'Azur.",
      logoUrl: null,
    },
  });

  const car1 = await prisma.car.create({
    data: {
      agencyId: agency1.id,
      make: "Peugeot",
      model: "208",
      year: 2022,
      mileageKm: 15000,
      transmission: "Manuelle",
      fuelType: "Essence",
      seats: 5,
      doors: 5,
      color: "Bleu",
      features: { gps: true, airConditioning: true },
      images: [
        "https://images.unsplash.com/photo-1550353127-b0da3aeaa0ca?auto=format&fit=crop&w=1200&q=60",
      ],
    },
  });

  const car2 = await prisma.car.create({
    data: {
      agencyId: agency1.id,
      make: "Renault",
      model: "Clio",
      year: 2021,
      mileageKm: 28000,
      transmission: "Automatique",
      fuelType: "Hybride",
      seats: 5,
      doors: 5,
      color: "Rouge",
      features: { bluetooth: true },
      images: [
        "https://images.unsplash.com/photo-1511914265872-c40672604a66?auto=format&fit=crop&w=1200&q=60",
      ],
    },
  });

  const car3 = await prisma.car.create({
    data: {
      agencyId: agency2.id,
      make: "BMW",
      model: "320d",
      year: 2019,
      mileageKm: 60000,
      transmission: "Automatique",
      fuelType: "Diesel",
      seats: 5,
      doors: 4,
      color: "Noir",
      features: { leather: true, sunroof: true },
      images: [
        "https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=60",
      ],
    },
  });

  await prisma.listing.create({
    data: {
      carId: car1.id,
      type: "SALE",
      status: "ACTIVE",
      priceCents: 1399900,
    },
  });

  await prisma.listing.create({
    data: {
      carId: car2.id,
      type: "RENT",
      status: "ACTIVE",
      dailyRateCents: 4900,
      availableFrom: new Date(),
      availableTo: null,
    },
  });

  await prisma.listing.create({
    data: {
      carId: car3.id,
      type: "SALE",
      status: "ACTIVE",
      priceCents: 2199900,
    },
  });

  console.log("Seed completed");
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });